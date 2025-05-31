<?php

namespace App\Service;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Contracts\Cache\CacheInterface;

class DiscordService
{
    private string $guildId;
    private string $guildUrl;
    private string $guestRoleId;
    private string $apiUrl;
    private string $discordToken;
    private ClientInterface $httpClient;

    public function __construct(
        ParameterBagInterface $params,
        private readonly CacheInterface $cache,
    ) {
        $this->httpClient = new Client();
        $this->guildId = $params->get('app.discord_guild_id');
        $this->discordToken = $params->get('app.discord_token');
        $this->guildUrl = "https://discord.com/api/guilds/{$this->guildId}";
        $this->apiUrl = "https://discord.com/api/v9";
        $this->guestRoleId = $params->get('app.discord_guest_role_id');
    }

    public function getDiscordGuildId(): string
    {
        return $this->guildId;
    }

    /**
     * Fetch Discord widget data including member count and invitation link
     */
    public function fetchWidgetData(): array
    {
        $defaultResponse = ['members' => [], 'invitation_link' => '#', 'total_members' => 0];
        try {
            $response = $this->httpClient->request('GET', "{$this->guildUrl}/widget.json");
        } catch (Exception $exception) {
            return $defaultResponse;
        }

        if ($response->getStatusCode() !== 200) {
            return $defaultResponse;
        }

        $data = json_decode($response->getBody()->getContents(), true);
        $inviteCode = str_replace('https://discord.com/invite/', '', $data['instant_invite'] ?? '');

        // If we got an invite code, fetch additional data about the invite
        $inviteData = [];
        if ($inviteCode) {
            $inviteData = $this->fetchInviteData($inviteCode);
        }

        return [
            'invitation_link' => $data['instant_invite'] ?? '#',
            'total_members' => $inviteData['approximate_member_count'] ?? 0,
            'invite_code' => $inviteCode,
            'members' => array_map(fn(mixed $item) => [
                'avatar_url' => $item['avatar_url'],
                'id' => (int)$item['id'],
                'username' => $item['username'],
            ], $data['members'] ?? []),
        ];
    }

    /**
     * Fetch detailed information about a Discord invite
     */
    private function fetchInviteData(string $inviteCode): array
    {
        if (empty($inviteCode)) {
            return [];
        }

        try {
            $response = $this->httpClient->request(
                'GET',
                "{$this->apiUrl}/invites/{$inviteCode}?with_counts=true&with_expiration=true"
            );
        } catch (Exception $exception) {
            return [];
        }

        if ($response->getStatusCode() !== 200) {
            return [];
        }

        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * Fetch upcoming Discord events
     */
    public function fetchUpcomingEvents(): array
    {
        $cacheKey = 'discord_upcoming_events';
        $cacheTtl = 300;

        $cachedEvents = $this->cache->get($cacheKey, function () use ($cacheTtl) {
            try {
                $response = $this->httpClient->request('GET', "{$this->guildUrl}/scheduled-events?with_user_count=true", [
                    'headers' => [
                        'Authorization' => "Bot {$this->discordToken}",
                        'Content-Type' => 'application/json',
                    ]
                ]);

                if ($response->getStatusCode() !== 200) {
                    return [];
                }

                $events = json_decode($response->getBody()->getContents(), true);

                usort($events, function ($a, $b) {
                    return strtotime($a['scheduled_start_time']) - strtotime($b['scheduled_start_time']);
                });

                return $events;
            } catch (Exception $e) {
                return [];
            }
        }, $cacheTtl);

        return $cachedEvents ?: [];
    }

    public function isGuest(string $userDiscordId): bool
    {
        $cacheKey = 'discord_is_guest_' . $userDiscordId;
        $cacheTtl = 120;

        $cachedEvents = $this->cache->get($cacheKey, function () use ($cacheTtl, $userDiscordId) {
            try {
                $response = $this->httpClient->request('GET', "{$this->guildUrl}/members/{$userDiscordId}", [
                    'headers' => [
                        'Authorization' => "Bot {$this->discordToken}",
                        'Content-Type' => 'application/json',
                    ]
                ]);

                if ($response->getStatusCode() !== 200) {
                    return false;
                }

                $data = json_decode($response->getBody()->getContents(), true);

                return in_array($this->guestRoleId, $data['roles'] ?? [], true);
            } catch (Exception $e) {
                return false;
            }
        }, $cacheTtl);

        return $cachedEvents ?: false;
    }
}
