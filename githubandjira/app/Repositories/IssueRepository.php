<?php

namespace App\Repositories;

use App\Clients\JiraClient;
use App\Factories\IssueFactory;
use Illuminate\Support\Collection;

class IssueRepository
{
    public function __construct(
        protected JiraClient $client,
        protected IssueFactory $factory
    ) {
    }

    /**
     * Haal issues op voor een specifiek project en release.
     */
    public function getIssuesByReleaseName(string $projectKey, string $releaseName): Collection
    {
        $response = $this->client->get('/search', [
            'jql'    => "project=\"{$projectKey}\" AND fixVersion=\"{$releaseName}\"",
            'fields' => 'summary,status,issuetype,issuelinks,parent,assignee',
        ]);

        return $this->factory->makeMultiple($response['issues'] ?? []);
    }

    /**
     * Haal alle subtasks op voor een parent issue.
     */
    public function getSubtasks(string $parentKey): Collection
    {
        $response = $this->client->get('/search', [
            'jql'       => "parent = \"{$parentKey}\"",
            'fields'    => 'summary,status,issuetype,parent,issuelinks,assignee',
            'maxResults' => 1000,
        ]);

        return $this->factory->makeMultiple($response['issues'] ?? []);
    }

    /**
     * Haal alle issues op die gelinkt zijn aan een issue via issuelinks.
     */
    public function getLinkedIssues(array $issueLinks): Collection
    {
        $linkedKeys = [];
        foreach ($issueLinks as $link) {
            if (isset($link['inwardIssue'])) {
                $linkedKeys[] = $link['inwardIssue']['key'];
            } elseif (isset($link['outwardIssue'])) {
                $linkedKeys[] = $link['outwardIssue']['key'];
            }
        }

        if (empty($linkedKeys)) {
            return collect();
        }

        $jql = 'issuekey in ("' . implode('","', $linkedKeys) . '")';
        $response = $this->client->get('/search', [
            'jql'       => $jql,
            'fields'    => 'summary,status,issuetype,parent,issuelinks,assignee',
            'maxResults' => 1000,
        ]);

        return $this->factory->makeMultiple($response['issues'] ?? []);
    }

    public function fetchReleaseInfo(string $projectKey, string $releaseName)
    {
        // Ophalen van alle versies (releases) voor het project:
        $releases = $this->client->get("project/{$projectKey}/versions");
        foreach ($releases as $release) {
            if ($release['name'] === $releaseName) {
                return $release;
            }
        }

        throw new \Exception("Release '$releaseName' niet gevonden in project $projectKey");
    }
}
