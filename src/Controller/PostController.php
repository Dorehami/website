<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Entity\Post;
use App\Entity\PostVote;
use App\Enum\WebhookEventAction;
use App\Form\CommentSubmissionType;
use App\Form\PostType;
use App\Message\WebhookEvent;
use App\Repository\PostRepository;
use App\Service\DiscordService;
use App\Service\UrlNormalizerService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/posts')]
class PostController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    )
    {
    }

    #[Route('/new', name: 'app_post_new', methods: ['GET', 'POST'])]
    #[IsGranted('user_action')]
    #[IsGranted('ROLE_USER')]
    public function new(
        Request $request,
        PostRepository $postRepository,
        UrlNormalizerService $urlNormalizer,
        MessageBusInterface $messageBus,
    ): Response {
        $post = new Post();
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $existingPost = $postRepository->findRecentByNormalizedUrl($post->getUrl());

            if ($existingPost) {
                $this->addFlash('info', 'این لینک قبلاً ارسال شده است. به صفحه مقاله قبلی هدایت می‌شوید.');
                return $this->redirectToRoute('app_post_show', ['id' => $existingPost->getId()]);
            }

            $normalizedUrl = $urlNormalizer->normalize($post->getUrl());
            $post->setNormalizedUrl($normalizedUrl);
            $post->setAuthor($this->getUser());
            $this->entityManager->persist($post);
            $this->entityManager->flush();

            $messageBus->dispatch(new WebhookEvent(
                WebhookEventAction::POST_PUBLISHED,
                [
                    'postId' => $post->getId(),
                    'author' => $this->getUser()->getUserIdentifier(),
                    'author_discord_id' => $this->getUser()->getDiscordId() ?? null,
                ]
            ));

            $this->addFlash('success', 'مقاله شما با موفقیت ارسال شد.');
            return $this->redirectToRoute('app_home');
        }

        return $this->render('post/new.html.twig', [
            'post' => $post,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_post_show')]
    public function show(
        Post $post,
        PostRepository $postRepository,
        DiscordService $discordService,
        Request $request,
    ): Response {
        $discordInfo = $discordService->fetchWidgetData();
        $discordEvents = $discordService->fetchUpcomingEvents();
        $mostPopularPosts = $postRepository->findMostPopularLastDay();
        
        $comment = new Comment();
        $form = $this->createForm(CommentSubmissionType::class, $comment);
        $form->handleRequest($request);
        
        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $comment->setPost($post);
                $comment->setAuthor($this->getUser());
                
                $this->entityManager->persist($comment);
                $this->entityManager->flush();

                $this->addFlash('success', 'دیدگاه شما با موفقیت ثبت شد.');
                return $this->redirectToRoute('app_post_show', ['id' => $post->getId()]);
            } else {
                $errors = $form->getErrors();
                foreach ($errors as $error) {
                    $this->addFlash('error', $error->getMessage());
                }
            }
        }

        return $this->render('post/show.html.twig', [
            'post' => $post,
            'discord_info' => $discordInfo,
            'discord_events' => $discordEvents,
            'most_popular_posts' => $mostPopularPosts,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/vote', name: 'app_post_vote', methods: ['POST'])]
    #[IsGranted('user_action')]
    #[IsGranted('ROLE_USER')]
    public function vote(Post $post, Request $request, EntityManagerInterface $entityManager): Response
    {
        // Check if the user has already voted
        if ($post->hasVotedBy($this->getUser())) {
            $this->addFlash('error', 'شما قبلاً به این مقاله رأی داده‌اید.');
            return $this->redirectToRoute('app_post_show', ['id' => $post->getId()]);
        }

        // Create new vote
        $vote = new PostVote();
        $vote->setUser($this->getUser());
        $vote->setPost($post);

        $entityManager->persist($vote);
        $entityManager->flush();

        $this->addFlash('success', 'رأی شما با موفقیت ثبت شد.');
        return $this->redirectToRoute('app_post_show', ['id' => $post->getId()]);
    }
}
