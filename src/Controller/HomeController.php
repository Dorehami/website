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
        $featuredPosts = $postRepository->findFeatured();
        $discordInfo = $discordService->fetchWidgetData();
        $discordEvents = $discordService->fetchUpcomingEvents();
        $mostPopularPosts = $postRepository->findMostPopularLastDay();

        $filter = $request->query->get('filter', 'newest');
        $page = max(1, (int)$request->query->get('page', 1));
        
        $defaultPageSize = 10;

        $result = match ($filter) {
            'popular' => $postRepository->findMostPopular($defaultPageSize, $page),
            default => $postRepository->findRecentPaginated($defaultPageSize, $page)
        };

        return $this->render('home/index.html.twig', [
            'filter' => $filter,
            'posts' => $result['posts'],
            'discord_info' => $discordInfo,
            'discord_events' => $discordEvents,
            'featured_posts' => $featuredPosts,
            'pagination' => $result['pagination'],
            'most_popular_posts' => $mostPopularPosts,
        ]);
    }

    #[Route('/rules', name: 'app_rules')]
    public function rules(): Response
    {
        return $this->render('home/rules.html.twig');
    }

    #[Route('/about', name: 'app_about')]
    public function about(): Response
    {
        return $this->render('home/about.html.twig');
    }

    #[Route('/faq', name: 'app_faq')]
    public function faq(): Response
    {
        return $this->render('home/faq.html.twig');
    }
}
