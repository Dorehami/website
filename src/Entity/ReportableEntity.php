<?php

namespace App\Entity;

use DateTimeImmutable;

interface ReportableEntity
{
    public function getContent(): ?string;

    public function setVisible(bool $isVisible): static;

    public function setModerationReason(?string $moderationReason): static;

    public function setModeratedAt(?DateTimeImmutable $moderatedAt): static;
}
