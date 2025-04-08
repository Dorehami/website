<?php

namespace App\Enum;

enum WebhookEventAction: string
{
    case POST_PUBLISHED = "POST_PUBLISHED";
    case USER_JOINED = "USER_JOINED";
}
