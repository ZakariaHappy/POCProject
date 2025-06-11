<?php

namespace App\Clients;

use App\Manager\IntegrationManager;
use http\Client;
use Illuminate\Support\Facades\Http;

class GitHubClient
{
    protected string $token;
    protected string $baseUrl;

    protected string $shaurl;

    protected string $repo;
    protected string $owner;

    public function __construct()
    {
        $integration = IntegrationManager::current();

        if (!$integration || !$integration->github_token) {
            throw new \Exception("Repository integratiegegevens ontbreken voor deze gebruiker");
        }

        $this->token = $integration->github_token;
        $this->owner = $integration->github_username;
        $this->baseUrl = config('github.url');
        $this->shaurl = config('github.shaurl');
    }

    public function get(string $endpoint, array $params = []): array
    {
        $response = Http::withToken($this->token)
            ->baseUrl($this->baseUrl)
            ->get($endpoint, $params);

        if ($response->failed()) {
            throw new \Exception("Repository API error ({$response->status()}): " . $response->body());
        }

        return $response->json();
    }

    public function post(string $endpoint, array $data = []): array
    {
        $response = Http::withToken($this->token)
            ->baseUrl($this->baseUrl)
            ->post($endpoint, $data);

        if ($response->failed()) {
            throw new \Exception("Repository API error ({$response->status()}): " . $response->body());
        }

        return $response->json();
    }

    public function getOwner(): string
    {
        return $this->owner;
    }

    public function getToken(): string
    {
        return $this->token;
    }
}
