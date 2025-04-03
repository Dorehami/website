<?php

namespace App\Twig\Extension;

use DateTime;
use DateTimeInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class TimeAgoExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('persian_ago', [$this, 'formatTimeAgo']),
            new TwigFilter('persian_number', [$this, 'formatPersianNumber']),
        ];
    }

    public function formatTimeAgo(DateTimeInterface|string $dateTime): string
    {
        if (is_string($dateTime)) {
            $dt = new DateTime($dateTime);
        } else {
            $dt = $dateTime;
        }
        
        $now = new DateTime();
        $diff = $now->getTimestamp() - $dt->getTimestamp();
        
        $prefix = $diff > 1 ? 'پیش' : 'دیگر';
        
        $diff = abs($diff);

        if ($diff < 60) {
            return "چند لحظه $prefix";
        }

        if ($diff < 3600) {
            $minutes = floor($diff / 60);
            return $this->formatPersianNumber($minutes) . "$prefix دقیقه ";
        }

        if ($diff < 86400) {
            $hours = floor($diff / 3600);
            return $this->formatPersianNumber($hours) . " ساعت $prefix";
        }

        if ($diff < 2592000) {
            $days = floor($diff / 86400);
            return $this->formatPersianNumber($days) . " روز $prefix";
        }

        if ($diff < 31536000) {
            $months = floor($diff / 2592000);
            return $this->formatPersianNumber($months) . " $prefix ماه ";
        }

        $years = floor($diff / 31536000);
        return $this->formatPersianNumber($years) . " سال $prefix";
    }

    public function formatPersianNumber(int $number): string
    {
        $persianDigits = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        $englishDigits = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];

        return str_replace($englishDigits, $persianDigits, (string)$number);
    }
}
