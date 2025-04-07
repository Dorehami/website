<?php

// src/Controller/SitemapController.php

namespace App\Controller;

use App\Repository\PostRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class SitemapController extends AbstractController
{
    #[Route('/sitemap.xml', name: 'app_sitemap', defaults: ['_format' => 'xml'])]
    public function sitemap(Request $request, PostRepository $postRepository): Response
    {
        $hostname = $request->getSchemeAndHttpHost();
        $posts = $postRepository->findRecent(daysWindow: 365);

        $urls = [];
        $urls[] = ['loc' => $this->generateUrl('app_home'), 'changefreq' => 'daily', 'priority' => '1.0'];
        $urls[] = ['loc' => $this->generateUrl('app_login'), 'changefreq' => 'monthly', 'priority' => '0.5'];

        foreach ($posts as $post) {
            $urls[] = [
                'loc' => $this->generateUrl('app_post_show', ['id' => $post->getId()]),
                'lastmod' => $post->getCreatedAt()->format('Y-m-d'),
                'changefreq' => 'weekly',
                'priority' => '0.7'
            ];
        }

        $response = new Response(
            $this->renderView('sitemap/index.xml.twig', [
                'urls' => $urls,
                'hostname' => $hostname
            ]),
            Response::HTTP_OK
        );

        $response->headers->set('Content-Type', 'text/xml');

        return $response;
    }

    #[Route('/robots.txt', name: 'app_robots_txt', defaults: ['_format' => 'txt'])]
    public function robots(): Response
    {
        return new Response(
            $this->renderView('sitemap/robots.txt.twig'),
            Response::HTTP_OK,
            ['Content-Type' => 'text/plain']
        );
    }

    #[Route('/feed.xml', name: 'app_rss_feed', defaults: ['_format' => 'xml'])]
    public function rssFeed(Request $request, PostRepository $postRepository): Response
    {
        $hostname = $request->getSchemeAndHttpHost();
        $posts = $postRepository->findNewest(limit: 20)['posts'];
        
        $response = new Response(
            $this->renderView('sitemap/feed.xml.twig', [
                'posts' => $posts,
                'hostname' => $hostname,
                'lastBuildDate' => new \DateTime(),
            ]),
            Response::HTTP_OK
        );
        
        $response->headers->set('Content-Type', 'application/rss+xml');
        
        return $response;
    }
}
