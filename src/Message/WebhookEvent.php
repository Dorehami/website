<?php

namespace App\Message;

use App\Enum\WebhookEventAction;

class WebhookEvent
{
    public function __construct(
        private readonly WebhookEventAction $action,
        private readonly array $payload,
    ) {
    }

    public function getAction(): WebhookEventAction
    {
        return $this->action;
    }

    public function getPayload(): array
    {
        return $this->payload;
    }
}
