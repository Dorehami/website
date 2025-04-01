<?php

namespace App\Controller;

use App\Repository\PostRepository;
use App\Service\DiscordService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(
        Request $request,
        PostRepository $postRepository,
        DiscordService $discordService,
    ): Response {
        $discordInfo = $discordService->fetchWidgetData();
        $discordEvents = $discordService->fetchUpcomingEvents();

        return $this->render('home/index.html.twig', [
            'discord_info' => $discordInfo,
            'discord_events' => $discordEvents,
        ]);
    }

    #[Route('/rules', name: 'app_rules')]
    public function rules()
    {
        return $this->render('home/rules.html.twig');
    }
}
