<?php

namespace App\Security;

use App\Entity\User;
use App\Enum\WebhookEventAction;
use App\Message\WebhookEvent;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use KnpU\OAuth2ClientBundle\Security\Authenticator\OAuth2Authenticator;
use Stevenmaguire\OAuth2\Client\Provider\KeycloakResourceOwner;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;

class KeycloakAuthenticator extends OAuth2Authenticator implements AuthenticationEntryPointInterface
{
    public function __construct(
        private readonly ClientRegistry $clientRegistry,
        private readonly EntityManagerInterface $entityManager,
        private readonly RouterInterface $router,
        private readonly UserRepository $userRepository,
        private readonly BannedUserChecker $bannedUserChecker,
        private readonly MessageBusInterface $messageBus,
        private readonly TokenStorageInterface $tokenStorage,
    ) {
    }

    public function supports(Request $request): ?bool
    {
        return $request->attributes->get('_route') === 'connect_auth_check';
    }

    public function authenticate(Request $request): Passport
    {
        $client = $this->clientRegistry->getClient('keycloak');
        $accessToken = $this->fetchAccessToken($client);

        return new SelfValidatingPassport(
            new UserBadge($accessToken->getToken(), function () use ($accessToken, $client) {
                /** @var KeycloakResourceOwner $kcUser */
                $kcUser = $client->fetchUserFromToken($accessToken);

                $currentUser = $this->tokenStorage->getToken()?->getUser();

                if ($currentUser instanceof User) {
                    $otherUser = $this->userRepository->findOneByEmail($kcUser->getEmail());
                    if ($otherUser && $otherUser->getId() !== $currentUser->getId()) {
                        throw new CustomUserMessageAuthenticationException('این حساب قبلاً به کاربر دیگری متصل شده است.');
                    }

                    $this->bannedUserChecker->checkPreAuth($currentUser);

                    $currentUser->setEmail($kcUser->getEmail());
                    if (method_exists($kcUser, 'getName')) {
                        $currentUser->setDisplayName($kcUser->getName());
                    }

                    $this->entityManager->persist($currentUser);
                    $this->entityManager->flush();

                    return $currentUser;
                }

                $existingUser = $this->userRepository->findOneByEmail($kcUser->getEmail());

                if ($existingUser) {
                    $this->bannedUserChecker->checkPreAuth($existingUser);

                    $this->entityManager->persist($existingUser);
                    $this->entityManager->flush();

                    return $existingUser;
                }

                $user = new User();

                $user->setEmail($kcUser->getEmail());
                if (method_exists($kcUser, 'getName')) {
                    $user->setDisplayName($kcUser->getName());
                }

                $user->setPassword('');
                $user->setRoles(['ROLE_USER']);

                $this->entityManager->persist($user);
                $this->entityManager->flush();

                $this->messageBus->dispatch(new WebhookEvent(
                    WebhookEventAction::USER_JOINED,
                    [
                        'userId' => $user->getId(),
                        'gate' => 'keycloak'
                    ]
                ));

                return $user;
            })
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        $targetUrl = $this->router->generate('app_home');

        return new RedirectResponse($targetUrl);
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        $message = strtr($exception->getMessageKey(), $exception->getMessageData());

        return new Response($message, Response::HTTP_FORBIDDEN);
    }

    public function start(Request $request, ?AuthenticationException $authException = null): Response
    {
        return new RedirectResponse(
            $this->router->generate('app_login'),
            Response::HTTP_TEMPORARY_REDIRECT
        );
    }
}
