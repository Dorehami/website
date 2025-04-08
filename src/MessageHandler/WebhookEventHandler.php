<?php

namespace App\MessageHandler;

use App\Message\WebhookEvent;
use App\Service\WebhookService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class WebhookEventHandler
{
    public function __construct(
        private readonly WebhookService $webhookService,
    ) {
    }

    public function __invoke(
        WebhookEvent $event,
    ) {
        $this->webhookService->callWebhook(
            action: $event->getAction()->value,
            payload: $event->getPayload()
        );
    }
}
