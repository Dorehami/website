<?php

namespace App\Repository;

use App\Entity\Report;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @extends ServiceEntityRepository<Report>
 */
class ReportRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Report::class);
    }

    /**
     * Creates a new report for the given entity.
     *
     * @param mixed $entity The entity being reported (must have getId() method)
     * @param User $by The user who submitted the report
     * @return Report The newly created report entity
     */
    public function report(
        mixed $entity,
        UserInterface $by,
    ): Report
    {
        $report = new Report();
        
        $report->setReportedBy($by);
        $report->setReportProcessed(false);
        $report->setAiFlagged(false);
        $report->setAiResult([]);

        $entityClass = get_class($entity);
        $report->setEntityType($entityClass);

        $entityId = $entity->getId();
        $report->setEntityId($entityId);
        
        $em = $this->getEntityManager();
        $em->persist($report);
        $em->flush();
        
        return $report;
    }
}
