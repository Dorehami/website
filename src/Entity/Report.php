<?php

namespace App\Entity;

use App\Repository\ReportRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ReportRepository::class)]
class Report
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $entityType = null;

    #[ORM\Column]
    private ?int $entityId = null;

    #[ORM\ManyToOne(inversedBy: 'reports')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $reportedBy = null;

    #[ORM\Column(nullable: true)]
    private ?array $aiResult = null;

    #[ORM\Column]
    private ?bool $aiFlagged = null;

    #[ORM\Column]
    private ?bool $reportProcessed = null;

    public function getEntityType(): ?string
    {
        return $this->entityType;
    }

    public function setEntityType(string $entityType): static
    {
        $this->entityType = $entityType;

        return $this;
    }

    public function getEntityId(): ?int
    {
        return $this->entityId;
    }

    public function setEntityId(int $entityId): static
    {
        $this->entityId = $entityId;

        return $this;
    }

    public function getReportedBy(): ?User
    {
        return $this->reportedBy;
    }

    public function setReportedBy(?User $reportedBy): static
    {
        $this->reportedBy = $reportedBy;

        return $this;
    }

    public function getAiResult(): ?array
    {
        return $this->aiResult;
    }

    public function setAiResult(?array $aiResult): static
    {
        $this->aiResult = $aiResult;

        return $this;
    }

    public function isAiFlagged(): ?bool
    {
        return $this->aiFlagged;
    }

    public function setAiFlagged(bool $aiFlagged): static
    {
        $this->aiFlagged = $aiFlagged;

        return $this;
    }

    public function isReportProcessed(): ?bool
    {
        return $this->reportProcessed;
    }

    public function setReportProcessed(bool $reportProcessed): static
    {
        $this->reportProcessed = $reportProcessed;

        return $this;
    }

}
