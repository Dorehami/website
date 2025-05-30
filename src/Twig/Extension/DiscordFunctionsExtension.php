<?php

namespace App\Twig\Extension;

use App\Service\DiscordService;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class DiscordFunctionsExtension extends AbstractExtension
{
    public function __construct(
        private readonly DiscordService $discordService
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('is_discord_guest_member', [$this, 'isGuest']),
        ];
    }

    public function isGuest(string|null $userDiscordId): bool
    {
        if (is_null($userDiscordId)) {
            return true;
        }

        return $this->discordService->isGuest($userDiscordId);
    }
}
