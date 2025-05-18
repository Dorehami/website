<?php

namespace App\Service;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class PlausibleAnalyticsService
{
    private string $apiUrl = 'https://analytics.aien.me/api/v2/query';
    private string $siteId = 'dorehami.dev';
    private string $authToken;
    private ClientInterface $client;

    public function __construct(ParameterBagInterface $parameterBag)
    {
        $this->client = new Client();
        $this->authToken = $parameterBag->get('app.analytics_token');
    }

    public function getVisitorsByPostId(int $postId, string|array $dateRange = "all"): array
    {
        $result = $this->getVisitorsByPostIds([$postId], $dateRange);
        return $result[$postId] ?? ['visits' => 0];
    }

    public function getVisitorsByPostIds(array $postIds, string|array $dateRange = "all"): array
    {
        $filters = array_map(fn($id) => "/posts/{$id}", $postIds);

        $response = $this->client->request('POST', $this->apiUrl, [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->authToken,
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'site_id' => $this->siteId,
                'metrics' => ['visitors'],
                'date_range' => $dateRange,
                'dimensions' => ['event:page'],
                'filters' => [['contains', 'event:page', $filters]],
            ],
        ]);

        $data = json_decode($response->getBody()->getContents(), true);

        $result = [];
        foreach ($data['results'] ?? [] as $row) {
            $pagePath = $row['dimensions'][0] ?? null;
            $visits = $row['metrics'][0] ?? 0;

            if ($pagePath && preg_match('#/posts/(\d+)#', $pagePath, $matches)) {
                $postId = $matches[1];
                $result[$postId] = [
                    'visits' => $visits
                ];
            }
        }

        return $result;
    }
}
