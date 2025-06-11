<?php

namespace App\Livewire;

use App\Repositories\IssueRepository;
use App\Services\JiraService;
use App\Services\ReleaseWorkflowService;
use Livewire\Component;

class JiraSectionComponent extends Component
{
    public ?string $jiraError = null;
    public ?string $releaseName = null;
    public ?string $projectKey = null;
    public array $issues = [];
    public array $releaseStatus = [
        'Accepted for Deployment' => [],
        'notAccepted' => [],
    ];

    public function fetchJiraReleaseIssues(JiraService $service, IssueRepository $repository): void
    {
        $this->jiraError = null;

        try {
            $releaseInfo = $repository->fetchReleaseInfo($this->projectKey, $this->releaseName);
            $releaseDate = $releaseInfo['releaseDate'] ?? null;

            // Fetch issues with details (hydrated)
            $mainIssues = $service->fetchIssuesWithDetails($this->projectKey, $this->releaseName);

            // Calculate release status
            $releaseStatus = $service->classifyStatus($mainIssues);

            $issuesArray = $mainIssues->map(fn($i) => $i->toArray())->all();
            $releaseStatusArray = [
                'Accepted for Deployment' => $releaseStatus['Accepted for Deployment']->map(fn($i) => $i->toArray())->all(),
                'notAccepted'             => $releaseStatus['notAccepted']->map(fn($i) => $i->toArray())->all(),
            ];

            $this->issues        = $issuesArray;
            $this->releaseStatus = $releaseStatusArray;

            // Nu met datum als tweede argument!
            $this->dispatch('jiraIssuesUpdated', $issuesArray);
            $this->dispatch('jiraReleaseNameUpdated', $this->releaseName, $releaseDate);
            $this->dispatch('jiraReleaseStatusUpdated', $releaseStatusArray);
        } catch (\Throwable $e) {
            $this->jiraError = 'Fout bij ophalen van release-issues: ' . $e->getMessage();
        }
    }



    public function render()
    {
        return view('livewire.jira-section-component', [
            'jiraError'     => $this->jiraError,
            'releaseStatus' => $this->releaseStatus,
        ]);
    }
}
