<?php

namespace App\Clients;

use App\Manager\IntegrationManager;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;

class JiraClient
{
    protected Client $client;

    public function __construct()
    {
        $integration = IntegrationManager::current();

        if (!$integration || !$integration->jira_email || !$integration->jira_token || !$integration->jira_domain) {
            throw new \Exception("Jira integratiegegevens ontbreken voor deze gebruiker.");
        }

        $this->client = new Client([
            'base_uri' => 'https://' . rtrim($integration->jira_domain, '/') . '/rest/api/3/',
            'auth' => [
                $integration->jira_email,
                $integration->jira_token
            ],
            'http_errors' => false,
        ]);
//        Log::info('Jira base URL:', ['url' => 'https://' . $integration->jira_domain]);
    }

    public function get(string $endpoint, array $query = []): array
    {
        try {
            $response = $this->client->request('GET', ltrim($endpoint, '/'), [
                'query' => $query,
            ]);

            if ($response->getStatusCode() >= 400) {
                throw new \Exception("Jira API-fout ({$response->getStatusCode()}): " . $response->getBody()->getContents());
            }

            return json_decode($response->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            throw new \Exception("Jira API-call mislukt: " . $e->getMessage(), $e->getCode(), $e);
        }
    }
}
