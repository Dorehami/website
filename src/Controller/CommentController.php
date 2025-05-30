<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Enum\WebhookEventAction;
use App\Message\Report;
use App\Message\WebhookEvent;
use App\Repository\ReportRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/comments')]
class CommentController extends AbstractController
{
    public function __construct(
        private readonly MessageBusInterface $bus,
    ) {
    }

    #[Route('/{id}/moderate', name: 'app_comment_moderate', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    #[IsGranted('user_action')]
    public function moderate(
        Comment $comment,
        ReportRepository $reportRepository,
    ): Response {
        $isAlreadyReported = $reportRepository->isAlreadyReported($comment);

        if ($isAlreadyReported) {
            $this->addFlash('success', 'گزارش شما ثبت شد');
            return $this->redirectToRoute('app_post_show', ['id' => $comment->getPost()->getId()]);
        }

        $reporter = $this->getUser();
        $report = $reportRepository->report($comment, $reporter);

        $this->bus->dispatch(
            new Report($report->getId())
        );

        $this->addFlash('success', 'گزارش شما ثبت شد');
        return $this->redirectToRoute('app_post_show', ['id' => $comment->getPost()->getId()]);
    }

    #[Route('/{id}/reply', name: 'app_comment_reply', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    #[IsGranted('user_action')]
    public function reply(
        Comment $parentComment,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        $content = trim($request->request->get('content', ''));

        if (empty($content)) {
            $this->addFlash('error', 'متن پاسخ نمی‌تواند خالی باشد.');
            return $this->redirectToRoute('app_post_show', ['id' => $parentComment->getPost()->getId()]);
        }

        $reply = new Comment();
        $reply->setContent($content);
        $reply->setAuthor($this->getUser());
        $reply->setPost($parentComment->getPost());
        $reply->setParent($parentComment);

        $entityManager->persist($reply);
        $entityManager->flush();

        $this->addFlash('success', 'پاسخ شما با موفقیت ثبت شد.');

        if ($parentComment->getAuthor()->isReceiveCommentReplyEmailNotification()) {
            $this->bus->dispatch(new WebhookEvent(
                WebhookEventAction::COMMENT_REPLY,
                [
                    'parentAuthor' => $parentComment->getAuthor()->getId(),
                    'authorId' => $this->getUser()->getUserIdentifier(),
                    'parentComment' => $parentComment->getId(),
                    'reply' => $reply->getId(),
                ]
            ));
        }

        if ($request->isXmlHttpRequest()) {
            return new JsonResponse([
                'success' => true,
                'message' => 'پاسخ شما با موفقیت ثبت شد.'
            ]);
        }

        return $this->redirectToRoute('app_post_show', ['id' => $parentComment->getPost()->getId()]);
    }
}
