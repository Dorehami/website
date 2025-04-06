<?php

namespace App\Contract;

interface ModerationServiceInterface
{
    /**
     * Check if content is appropriate (not violating moderation policies)
     *
     * @param string $content The content to moderate
     * @return bool True if the content is appropriate, false if it violates moderation policies
     */
    public function isAppropriate(string $content): bool;

    /**
     * Get detailed moderation results for the given content
     *
     * @param string $content The content to moderate
     * @return array Detailed moderation results with categories and scores
     */
    public function getDetailedResults(string $content): array;
}
