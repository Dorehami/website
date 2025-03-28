<?php

namespace App\Security;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use KnpU\OAuth2ClientBundle\Security\Authenticator\OAuth2Authenticator;
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
use Wohali\OAuth2\Client\Provider\DiscordResourceOwner;

class DiscordAuthenticator extends OAuth2Authenticator implements AuthenticationEntryPointInterface
{
    public function __construct(
        private readonly ClientRegistry $clientRegistry,
        private readonly EntityManagerInterface $entityManager,
        private readonly RouterInterface $router,
        private readonly UserRepository $userRepository,
        private readonly BannedUserChecker $bannedUserChecker
    )
    {
    }

    public function supports(Request $request): ?bool
    {
        return $request->attributes->get('_route') === 'connect_discord_check';
    }

    public function authenticate(Request $request): Passport
    {
        $client = $this->clientRegistry->getClient('discord');
        $accessToken = $this->fetchAccessToken($client);

        return new SelfValidatingPassport(
            new UserBadge($accessToken->getToken(), function () use ($accessToken, $client) {
                /** @var DiscordResourceOwner $discordUser */
                $discordUser = $client->fetchUserFromToken($accessToken);

                // Check if user already exists
                $existingUser = $this->userRepository->findOneByDiscordId($discordUser->getId());

                if ($existingUser) {
                    $this->bannedUserChecker->checkPreAuth($existingUser);
                    $existingUser->setDiscordUsername($discordUser->getUsername());
                    if ($discordUser->getAvatarHash()) {
                        $existingUser->setAvatarUrl('https://cdn.discordapp.com/avatars/' . $discordUser->getId() . '/' . $discordUser->getAvatarHash() . '.png');
                    }
                    $this->entityManager->persist($existingUser);
                    $this->entityManager->flush();

                    return $existingUser;
                }

                // User doesn't exist, create a new one
                $email = $discordUser->getEmail();

                // Check if user with this email already exists
                if ($email) {
                    $userWithEmail = $this->userRepository->findOneByEmail($email);
                    if ($userWithEmail) {
                        $this->bannedUserChecker->checkPreAuth($userWithEmail);
                        // Connect discord to existing account
                        $userWithEmail->setDiscordId($discordUser->getId());
                        $userWithEmail->setDiscordUsername($discordUser->getUsername());
                        if ($discordUser->getAvatarHash()) {
                            $userWithEmail->setAvatarUrl('https://cdn.discordapp.com/avatars/' . $discordUser->getId() . '/' . $discordUser->getAvatarHash() . '.png');
                        }
                        $this->entityManager->persist($userWithEmail);
                        $this->entityManager->flush();

                        return $userWithEmail;
                    }
                }

                // Create new user
                $user = new User();
                $user->setDiscordId($discordUser->getId());
                $user->setDiscordUsername($discordUser->getUsername());
                if ($email) {
                    $user->setEmail($email);
                } else {
                    // Generate a placeholder email if none provided
                    $user->setEmail('discord_' . $discordUser->getId() . '@example.com');
                }
                if ($discordUser->getAvatarHash()) {
                    $user->setAvatarUrl('https://cdn.discordapp.com/avatars/' . $discordUser->getId() . '/' . $discordUser->getAvatarHash() . '.png');
                }
                // Use a blank password since login is via Discord
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
