<?php

namespace App\Service;

use App\Contract\ModerationServiceInterface;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class OpenAIModerationService implements ModerationServiceInterface
{
    private readonly ClientInterface $client;
    
    private const string API_URL = 'https://api.openai.com/v1/moderations';
    private string $apiKey;

    public function __construct(
        ParameterBagInterface $parameterBag,
    ) {
        $this->apiKey = $parameterBag->get('app.openai_api_key');
        $this->client = new Client([
            'timeout' => 10,
            'max_duration' => 30
        ]);
    }

    public function isAppropriate(string $content): bool
    {
        $result = $this->moderateContent($content);

        return !($result['results'][0]['flagged'] ?? true);
    }

    public function getDetailedResults(string $content): array
    {
        $result = $this->moderateContent($content);

        return $result['results'][0] ?? ['error' => 'Failed to get moderation results'];
    }

    private function moderateContent(string $content): array
    {
        try {
            $response = $this->client->request('POST', self::API_URL, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->apiKey,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'input' => $content,
                ],
            ]);

            return $response->toArray();
        } catch (\Exception $e) {
            return ['results' => [['flagged' => true, 'error' => $e->getMessage()]]];
        }
    }
}