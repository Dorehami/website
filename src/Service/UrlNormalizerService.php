<?php

namespace App\Service;

class UrlNormalizerService
{
    /**
     * Normalize URLs to treat slight variations as the same URL
     */
    public function normalize(string $url): string
    {
        $url = preg_replace('/#.*$/', '', $url);
        $url = preg_replace('/[?&](utm_source|utm_medium|utm_campaign|utm_term|utm_content)=[^&]*/', '', $url);
        $url = rtrim($url, '/');
        $url = preg_replace('/^https?:\/\//', '', $url);
        $url = preg_replace('/^www\./', '', $url);
        
        return $url;
    }
}