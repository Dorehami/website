<?php

namespace App\Service;

use DateInterval;
use DatePeriod;
use DateTime;

class UtilityService
{
    /**
     * Normalize URLs to treat slight variations as the same URL
     */
    public function normalizeUrl(string $url): string
    {
        $url = preg_replace('/#.*$/', '', $url);
        $url = preg_replace('/[?&](utm_source|utm_medium|utm_campaign|utm_term|utm_content)=[^&]*/', '', $url);
        $url = rtrim($url, '/');
        $url = preg_replace('/^https?:\/\//', '', $url);
        $url = preg_replace('/^www\./', '', $url);

        return $url;
    }

    public function generateDates(string $start = 'now', string $direction = '-1 day', int $count = 30): array
    {
        $dates = [];

        $today = new DateTime($start);
        $interval = DateInterval::createFromDateString($direction);
        $period = new DatePeriod($today, $interval, $count);

        foreach ($period as $date) {
            $dates[] = $date->format('Y-m-d');
        }

        return $dates;
    }
}
