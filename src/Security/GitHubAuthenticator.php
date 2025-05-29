<?php

namespace App\Security;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use KnpU\OAuth2ClientBundle\Security\Authenticator\OAuth2Authenticator;
use League\OAuth2\Client\Provider\GithubResourceOwner;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;

class GitHubAuthenticator extends OAuth2Authenticator implements AuthenticationEntryPointInterface
{
    public function __construct(
        private readonly ClientRegistry $clientRegistry,
        private readonly EntityManagerInterface $entityManager,
        private readonly RouterInterface $router,
        private readonly UserRepository $userRepository,
        private readonly BannedUserChecker $bannedUserChecker
    ) {
    }

    public function supports(Request $request): ?bool
    {
        return $request->attributes->get('_route') === 'connect_github_check';
    }

    public function authenticate(Request $request): Passport
    {
        $client = $this->clientRegistry->getClient('github');
        $accessToken = $this->fetchAccessToken($client);

        return new SelfValidatingPassport(
            new UserBadge($accessToken->getToken(), function () use ($accessToken, $client) {
                /** @var GithubResourceOwner $githubUser */
                $githubUser = $client->fetchUserFromToken($accessToken);

                $existingUser = $this->userRepository->findOneByEmail($githubUser->getEmail());

                if ($existingUser) {
                    $this->bannedUserChecker->checkPreAuth($existingUser);
                    $existingUser->setGithubId($githubUser->getId());
                    $existingUser->setGithubUsername($githubUser->getNickname());

                    if (empty($existingUser->getDisplayName())) {
                        $existingUser->setDisplayName($githubUser->getName());
                    }

                    $this->entityManager->persist($existingUser);
                    $this->entityManager->flush();

                    return $existingUser;
                }

                $user = new User();
                $user->setGithubId($githubUser->getId());
                $user->setGithubUsername($githubUser->getNickname());
                if ($githubUser->getEmail()) {
                    $user->setEmail($githubUser->getEmail());
                } else {
                    $user->setEmail('github_' . $githubUser->getId() . '@example.com');
                }

                $user->setPassword('');
                $user->setRoles(['ROLE_USER']);

                $this->entityManager->persist($user);
                $this->entityManager->flush();

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
