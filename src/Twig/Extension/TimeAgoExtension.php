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

    public function formatTimeAgo(DateTimeInterface $dateTime): string
    {
        $now = new DateTime();
        $diff = $now->getTimestamp() - $dateTime->getTimestamp();

        if ($diff < 60) {
            return 'چند لحظه پیش';
        }

        if ($diff < 3600) {
            $minutes = floor($diff / 60);
            return $this->formatPersianNumber($minutes) . ' دقیقه پیش';
        }

        if ($diff < 86400) {
            $hours = floor($diff / 3600);
            return $this->formatPersianNumber($hours) . ' ساعت پیش';
        }

        if ($diff < 2592000) {
            $days = floor($diff / 86400);
            return $this->formatPersianNumber($days) . ' روز پیش';
        }

        if ($diff < 31536000) {
            $months = floor($diff / 2592000);
            return $this->formatPersianNumber($months) . ' ماه پیش';
        }

        $years = floor($diff / 31536000);
        return $this->formatPersianNumber($years) . ' سال پیش';
    }

    public function formatPersianNumber(int $number): string
    {
        $persianDigits = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        $englishDigits = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];

        return str_replace($englishDigits, $persianDigits, (string)$number);
    }
}