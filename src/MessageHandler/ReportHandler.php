<?php

namespace App\MessageHandler;

use App\Contract\ModerationServiceInterface;
use App\Entity\ReportableEntity;
use App\Message\Report;
use App\Repository\ReportRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class ReportHandler
{
    public function __construct(
        private readonly ReportRepository $reportRepository,
        private readonly ModerationServiceInterface $moderationService,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function __invoke(
        Report $report,
    ) {
        $report = $this->reportRepository->find($report->getReportId());

        $qb = $this->entityManager->createQueryBuilder();
        $qb->select('e')
            ->from($report->getEntityType(), 'e')
            ->where('e.id = :entityId')
            ->setParameter('entityId', $report->getEntityId());

        /** @var ReportableEntity $entity */
        $entity = $qb->getQuery()->getOneOrNullResult();

        $details = $this->moderationService->getDetailedResults($entity->getContent());

        $isFlagged = $details['flagged'] ?? false;
        $reasons = $details['categories'] ?? [];

        $report->setAiFlagged($isFlagged);
        $report->setReportProcessed(true);
        $report->setAiResult($reasons);

        $this->entityManager->persist($report);

        if (!$isFlagged) {
            $this->entityManager->flush();
            return;
        }

        $entity->setModeratedAt(new DateTimeImmutable());
        $entity->setModerationReason("BY AI: " . $reasons);

        $this->entityManager->persist($entity);
        $this->entityManager->flush();
    }
}
