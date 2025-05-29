<?php

namespace App\Enum;

enum WebhookEventAction: string
{
    case POST_PUBLISHED = "POST_PUBLISHED";
    case USER_JOINED = "USER_JOINED";
    case POST_UPVOTE = "POST_UPVOTE";
    case POST_COMMENTED = "POST_COMMENTED";
    case COMMENT_REPLY = "COMMENT_REPLY";
}
