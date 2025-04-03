<?php

namespace App\Controller;

use App\Service\DiscordService;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class DiscordController extends AbstractController
{
    #[Route('/connect/discord', name: 'connect_discord')]
    public function connectAction(ClientRegistry $clientRegistry): RedirectResponse
    {
        return $clientRegistry
            ->getClient('discord')
            ->redirect([
                'identify', 'email'
            ]);
    }

    #[Route('/connect/discord/check', name: 'connect_discord_check')]
    public function connectCheckAction(Request $request, ClientRegistry $clientRegistry): RedirectResponse
    {
        // This route is handled by the DiscordAuthenticator
        // Will not be called unless authentication fails
        return $this->redirectToRoute('app_home');
    }

    #[Route('/redirect/invite', name: 'redirect_invite')]
    public function redirectToDiscord(DiscordService $discordService): RedirectResponse
    {
        $info = $discordService->fetchWidgetData();

        return $this->redirect($info['invitation_link']);
    }

    #[Route('/redirect/event/{eventId}', name: 'redirect_event')]
    public function redirectToEvent(DiscordService $discordService, string $eventId): RedirectResponse
    {
        $guild = $discordService->getDiscordGuildId();
        
        return $this->redirect("https://discord.com/events/$guild/$eventId");
    }
}
