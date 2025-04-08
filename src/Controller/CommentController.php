<?php

namespace App\Controller;

use App\Entity\Comment;
use App\Message\Report;
use App\Repository\ReportRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/comments')]
class CommentController extends AbstractController
{
    #[Route('/{id}/moderate', name: 'app_comment_moderate', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    #[IsGranted('user_action')]
    public function moderate(
        Comment $comment,
        MessageBusInterface $bus,
        ReportRepository $reportRepository,
    ): Response {
        $isAlreadyReported = $reportRepository->isAlreadyReported($comment);

        if ($isAlreadyReported) {
            $this->addFlash('success', 'گزارش شما ثبت شد');
            return $this->redirectToRoute('app_post_show', ['id' => $comment->getPost()->getId()]);
        }

        $reporter = $this->getUser();
        $report = $reportRepository->report($comment, $reporter);

        $bus->dispatch(
            new Report($report->getId())
        );

        $this->addFlash('success', 'گزارش شما ثبت شد');
        return $this->redirectToRoute('app_post_show', ['id' => $comment->getPost()->getId()]);
    }
}
