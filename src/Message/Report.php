<?php

namespace App\Message;

class Report
{
    public function __construct(
        private readonly int $reportId,
    ) {
    }

    public function getReportId(): int
    {
        return $this->reportId;
    }
}
