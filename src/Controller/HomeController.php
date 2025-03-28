<?php

namespace App\Controller;

use App\Repository\PostRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(Request $request, PostRepository $postRepository): Response
    {
        $filter = $request->query->get('filter', 'newest');

        $posts = match ($filter) {
            'popular' => $postRepository->findMostPopular(10),
            default => $postRepository->findNewest(10)
        };

        // Note: In a real application, you'd want to use a Discord API 
        // to get the actual number of users in your Discord server.
        $discordUsers = 2500; // Placeholder

        return $this->render('home/index.html.twig', [
            'posts' => $posts,
            'discord_users' => $discordUsers,
            'filter' => $filter
        ]);
    }
}