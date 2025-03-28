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
    ): Response
    {
        $filter = $request->query->get('filter', 'newest');
        $page = max(1, (int)$request->query->get('page', 1));

        $result = match ($filter) {
            'popular' => $postRepository->findMostPopular(10, $page),
            default => $postRepository->findNewest(10, $page)
        };

        $discordInfo = $discordService->fetchWidgetData();

        return $this->render('home/index.html.twig', [
            'posts' => $result['posts'],
            'pagination' => $result['pagination'],
            'discord_info' => $discordInfo,
            'filter' => $filter
        ]);
    }
}
