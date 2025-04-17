<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MergeProposalService
{
    protected $token;
    protected $repo;

    public function __construct()
    {
        $integration = auth()->user()?->integration;

        if (!$integration || !$integration->github_token || !$integration->github_repo) {
            $this->token = null;
            $this->repo = null;
            return;
        }

        $this->token = $integration->github_token;
        $this->repo = $integration->github_repo;
    }


    public function createMergeProposal($branchName, $releaseName)
    {
        if (!$this->token || !$this->repo) {
            return [
                'success' => false,
                'error' => 'GitHub integratiegegevens ontbreken of zijn incompleet.',
                'details' => 'Je moet je GitHub token en repository instellen in je integratie-instellingen.'
            ];
        }

        $releaseName = str_replace(' ', '-', $releaseName);

        Log::info("Bezig met aanmaken PR", [
            'head' => $branchName,
            'base' => $releaseName,
            'repo' => $this->repo
        ]);

        $response = Http::withToken($this->token)->post("https://api.github.com/repos/{$this->repo}/pulls", [
            'title' => "Merge {$branchName} naar {$releaseName}",
            'head' => $branchName,
            'base' => $releaseName,
            'body' => "Automatisch mergevoorstel voor Jira issue {$branchName}.",
        ]);

        if (!$response->successful()) {
            Log::error("❌ PR aanmaken mislukt voor {$branchName}: " . $response->body());

            return [
                'success' => false,
                'error' => $response->json()['message'] ?? 'Onbekende fout',
                'details' => $response->body()
            ];
        }

        $pullRequest = $response->json();
        $prNumber = $pullRequest['number'];
        $prUrl = $pullRequest['html_url'];

        sleep(2);

        $detailsResponse = Http::withToken($this->token)->get("https://api.github.com/repos/{$this->repo}/pulls/{$prNumber}");

        if ($detailsResponse->successful()) {
            $mergeable = $detailsResponse->json()['mergeable'];

            return [
                'success' => true,
                'url' => $prUrl,
                'mergeable' => $mergeable,
            ];
        } else {
            Log::warning("⚠️ Kon mergeable status niet ophalen voor PR #{$prNumber}");

            return [
                'success' => true,
                'url' => $prUrl,
                'mergeable' => null
            ];
        }
    }
}
