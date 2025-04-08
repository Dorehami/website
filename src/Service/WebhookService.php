<?php

namespace App\Service;

use DateTimeImmutable;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class WebhookService
{
    private ClientInterface $httpClient;
    private string $webhookUrl;
    private string $webhookSecret;

    public function __construct(
        ParameterBagInterface $params,
        private readonly LoggerInterface $logger,
    ) {
        $this->httpClient = new Client();
        $this->webhookUrl = $params->get('app.webhook_url');
        $this->webhookSecret = $params->get('app.webhook_secret');
    }

    /**
     * Make the call to the webhook URL
     *
     * @param string $action The type of event being dispatched
     * @param array $payload The data payload to send with the event
     * @return bool Whether the call was successful
     */
    public function callWebhook(string $action, array $payload): bool
    {
        if (empty($this->webhookUrl) || empty($this->webhookSecret)) {
            $this->logger->warning('Webhook call attempted but URL or secret is not configured');
            return false;
        }

        try {
            $response = $this->httpClient->request('POST', $this->webhookUrl, [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'x-auth' => $this->webhookSecret,
                ],
                'json' => [
                    'action' => $action,
                    'timestamp' => new DateTimeImmutable()->format('c'),
                    'data' => $payload,
                ],
            ]);

            $statusCode = $response->getStatusCode();

            if ($statusCode >= 200 && $statusCode < 300) {
                return true;
            }

            $this->logger->error('Webhook call failed', [
                'status_code' => $statusCode,
                'response' => json_decode($response->getBody()->getContents(), true),
            ]);

            return false;
        } catch (Exception $e) {
            $this->logger->error('Exception occurred while calling webhook', [
                'exception' => $e->getMessage(),
            ]);

            return false;
        }
    }
}
