<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Repository\PostRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/comments')]
class CommentController extends AbstractController
{
    #[Route('/post/{postId}', name: 'app_comment_new', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    #[IsGranted('user_action')]
    public function new(int $postId, Request $request, EntityManagerInterface $entityManager, PostRepository $postRepository): Response
    {
        $post = $postRepository->find($postId);

        if (!$post) {
            throw $this->createNotFoundException('The post does not exist');
        }

        $content = $request->request->get('content');

        if (empty($content)) {
            $this->addFlash('error', 'متن دیدگاه نمی‌تواند خالی باشد.');
            return $this->redirectToRoute('app_post_show', ['id' => $postId]);
        }

        $comment = new Comment();
        $comment->setContent($content);
        $comment->setAuthor($this->getUser());
        $comment->setPost($post);

        $entityManager->persist($comment);
        $entityManager->flush();

        $this->addFlash('success', 'دیدگاه شما با موفقیت ثبت شد.');
        return $this->redirectToRoute('app_post_show', ['id' => $postId]);
    }
}