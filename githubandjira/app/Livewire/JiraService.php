<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Http;

class JiraService
{
    protected $email;
    protected $token;
    protected $domain;

    public function __construct()
    {

        $integration = auth()->user()?->integration;

        if (
            !$integration ||
            empty($integration->jira_email) ||
            empty($integration->jira_token) ||
            empty($integration->jira_domain)
        ) {
            throw new \Exception('Jira integratiegegevens ontbreken of zijn incompleet.');
        }

        $this->email = $integration->jira_email;
        $this->token = $integration->jira_token;
        $this->domain = $integration->jira_domain;
    }

    public function isConfigured(): bool
    {
        return $this->email && $this->token && $this->domain;
    }


    public function fetchProject($projectKey)
    {
        $response = Http::withBasicAuth($this->email, $this->token)
            ->get("https://{$this->domain}/rest/api/3/project/{$projectKey}");

        return $response->successful() ? $response->json() : null;
    }

    public function fetchIssues($projectKey)
    {
        $response = Http::withBasicAuth($this->email, $this->token)
            ->get("https://{$this->domain}/rest/api/3/search", [
                'jql' => 'project="' . $projectKey . '"',
                'fields' => 'summary,status,issuetype,issuelinks'
            ]);

        return $response->successful() ? $response->json()['issues'] : [];
    }



    public function fetchReleaseIssues($projectKey, $releaseName)
    {
        $versionsResponse = Http::withBasicAuth($this->email, $this->token)
            ->get("https://{$this->domain}/rest/api/3/project/{$projectKey}/versions");

        if (!$versionsResponse->successful()) {
            return [];
        }

        $versions = $versionsResponse->json();
        $version = collect($versions)->firstWhere('name', $releaseName);

        if (!$version) {
            return [];
        }

        // Stap 1: Haal alle issues op voor deze release
        $issuesResponse = Http::withBasicAuth($this->email, $this->token)
            ->get("https://{$this->domain}/rest/api/3/search", [
                'jql' => 'project="' . $projectKey . '" AND fixVersion="' . $releaseName . '"',
                'fields' => 'summary,status,issuetype,issuelinks,parent'
            ]);

        if (!$issuesResponse->successful()) {
            return [];
        }

        $releaseIssues = $issuesResponse->json()['issues'];
        $extraIssues = [];

        foreach ($releaseIssues as $issue) {
            // 🔗 1. Linked issues ophalen
            foreach ($issue['fields']['issuelinks'] ?? [] as $link) {
                $linkedKey = $link['inwardIssue']['key'] ?? $link['outwardIssue']['key'] ?? null;

                if ($linkedKey) {
                    $linked = $this->fetchIssueByKey($linkedKey);
                    if ($linked) {
                        $extraIssues[] = $linked;
                    }
                }
            }

            // 👶 2. Subtasks ophalen als dit GEEN subtask is
            if (!($issue['fields']['issuetype']['subtask'] ?? false)) {
                $subtasks = $this->fetchSubtasks($issue['key']);
                $extraIssues = array_merge($extraIssues, $subtasks);
            }
        }

        return array_merge($releaseIssues, $extraIssues);
    }

    public function fetchIssueByKey($key)
    {
        $response = Http::withBasicAuth($this->email, $this->token)
            ->get("https://{$this->domain}/rest/api/3/issue/{$key}?fields=summary,status,issuetype,parent,issuelinks");

        return $response->successful() ? $response->json() : null;
    }

    public function fetchSubtasks($parentKey)
    {
        $response = Http::withBasicAuth($this->email, $this->token)
            ->get("https://{$this->domain}/rest/api/3/search", [
                'jql' => 'parent = "' . $parentKey . '"',
                'fields' => 'summary,status,issuetype,parent,issuelinks'
            ]);

        return $response->successful() ? $response->json()['issues'] : [];
    }
}
