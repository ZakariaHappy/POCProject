<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Http;

class GitHubService
{
    protected $token;
    protected $username;
    protected $repo;

    public function getToken()
    {
        return $this->token;
    }

    public function getRepo()
    {
        return $this->repo;
    }


    public function __construct()
    {
        $integration = auth()->user()?->integration;

        if (!$integration || !$integration->github_token || !$integration->github_username || !$integration->github_repo) {
            // Niet crashen — gewoon properties op null zetten
            $this->token = null;
            $this->username = null;
            $this->repo = null;
            return;
        }

        $this->token = $integration->github_token;
        $this->username = $integration->github_username;
        $this->repo = $integration->github_repo;
    }

    public function isConfigured(): bool
    {
        return $this->token && $this->username && $this->repo;
    }

    public function fetchRepositories($repoName)
    {
        $response = Http::withToken($this->token)
            ->get("https://api.github.com/search/repositories", [
                'q' => "user:{$this->username} {$repoName}",
            ]);

        if ($response->successful()) {
            return $response->json()['items'] ?? [];
        } else {
            throw new \Exception('GitHub API error: ' . $response->body());
        }
    }


    public function fetchBranches($fullRepoName)
    {
        $allBranches = [];
        $page = 1;

        while (true) {
            $response = Http::withToken($this->token)
                ->get("https://api.github.com/repos/{$fullRepoName}/branches", [
                    'per_page' => 100,
                    'page' => $page,
                ]);

            if ($response->successful()) {
                $branches = $response->json();
                if (empty($branches)) {
                    break;
                }

                $allBranches = array_merge($allBranches, $branches);
                $page++;
            } else {
                \Log::error("Branches ophalen mislukt voor {$fullRepoName}: " . $response->body());
                return [];
            }
        }

        return $allBranches;
    }
}
