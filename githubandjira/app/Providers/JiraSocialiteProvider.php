<?php

namespace App\Providers;

use Laravel\Socialite\Two\AbstractProvider;
use Laravel\Socialite\Two\ProviderInterface;
use Laravel\Socialite\Two\User;

class JiraSocialiteProvider extends AbstractProvider implements ProviderInterface
{
    protected $scopes = ['read:jira-user', 'read:jira-work'];

    protected function getAuthUrl($state)
    {
        return $this->buildAuthUrlFromBase('https://auth.atlassian.com/authorize', $state);
    }

    protected function getTokenUrl()
    {
        return 'https://auth.atlassian.com/oauth/token';
    }

    protected function getUserByToken($token)
    {
        $response = $this->getHttpClient()->get('https://api.atlassian.com/me', [
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Accept' => 'application/json',
            ],
        ]);

        return json_decode($response->getBody()->getContents(), true);
    }

    protected function mapUserToObject(array $user)
    {
        return (new User())->setRaw($user)->map([
            'id' => $user['account_id'] ?? null,
            'nickname' => $user['nickname'] ?? null,
            'name' => $user['displayName'] ?? null,
            'email' => $user['emailAddress'] ?? null,
            'avatar' => $user['avatarUrls']['48x48'] ?? null,
        ]);
    }
}
