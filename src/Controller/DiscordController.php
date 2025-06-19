<?php

namespace App\Controller;

use App\Service\DiscordService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Attribute\Route;

class DiscordController extends AbstractController
{
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
