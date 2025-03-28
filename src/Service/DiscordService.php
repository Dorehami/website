<?php

namespace App\Service;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class DiscordService
{
    private string $guildId;
    private string $guildUrl;
    private string $apiUrl;
    private string $discordToken;
    private ClientInterface $httpClient;

    public function __construct(
        private readonly ParameterBagInterface $params
    ) {
        $this->httpClient = new Client();
        $this->guildId = $params->get('app.discord_guild_id');
        $this->discordToken = $params->get('app.discord_token');
        $this->guildUrl = "https://discord.com/api/guilds/{$this->guildId}";
        $this->apiUrl = "https://discord.com/api/v9";
    }

    /**
     * Get channels with their information including user counts
     */
    public function getChannels(): array
    {
        $response = $this->httpClient->request('GET', "{$this->guildUrl}/channels", [
            'headers' => [
                'Authorization' => "Bot {$this->discordToken}",
                'Content-Type' => 'application/json',
            ]
        ]);

        if ($response->getStatusCode() !== 200) {
            return [];
        }

        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * Get the total number of Discord members and online members
     */
    public function getMemberCount(): array
    {
        $widgetData = $this->fetchWidgetData();

        return [
            'total' => $widgetData['total_members'],
            'online' => $widgetData['members_online'],
            'invitation_link' => $widgetData['invitation_link']
        ];
    }

    /**
     * Fetch Discord widget data including member count and invitation link
     */
    public function fetchWidgetData(): array
    {
        $response = $this->httpClient->request('GET', "{$this->guildUrl}/widget.json");

        if ($response->getStatusCode() !== 200) {
            return ['members_online' => 0, 'invitation_link' => '#'];
        }

        $data = json_decode($response->getBody()->getContents(), true);
        $inviteCode = str_replace('https://discord.com/invite/', '', $data['instant_invite'] ?? '');

        // If we got an invite code, fetch additional data about the invite
        $inviteData = [];
        if ($inviteCode) {
            $inviteData = $this->fetchInviteData($inviteCode);
        }

        return [
            'members_online' => $data['presence_count'] ?? 0,
            'invitation_link' => $data['instant_invite'] ?? '#',
            'total_members' => $inviteData['approximate_member_count'] ?? 0,
            'invite_code' => $inviteCode
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

        $response = $this->httpClient->request(
            'GET',
            "{$this->apiUrl}/invites/{$inviteCode}?with_counts=true&with_expiration=true"
        );

        if ($response->getStatusCode() !== 200) {
            return [];
        }

        return json_decode($response->getBody()->getContents(), true);
    }
}
