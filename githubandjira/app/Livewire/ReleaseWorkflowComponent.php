<?php

namespace App\Livewire;

use App\Services\GitHubService;
use App\Services\ReleaseWorkflowService;
use Livewire\Component;

class ReleaseWorkflowComponent extends Component
{
    public array $issues = [];
    public array $branches = [];
    public array $matchedBranches = [];
    public ?string $releaseName = null;
    public array $pullRequestResults = [];
    public ?string $selectedRepo = null;
    public int $currentStep = 1;
    public array $releaseStatus = [];
    public bool $releaseBranchExists = false;

    public ?string $releaseBranchFormatted = null;

    public ?string $releaseDate = null;

    public array $unmatchedIssues = [];
    protected $listeners = [
        'jiraIssuesUpdated'         => 'onJiraIssuesUpdated',
        'jiraReleaseStatusUpdated'  => 'onJiraReleaseStatusUpdated',
        'githubBranchesUpdated'     => 'onGithubBranchesUpdated',
        'jiraReleaseNameUpdated'    => 'onJiraReleaseNameUpdated',
        'setGithubRepo'             => 'onSetGithubRepo',
    ];

    // Actions handled via Service:
    public function onSetGithubRepo(string $repo): void
    {
        $this->selectedRepo = $repo;
        session(['release.selectedRepo' => $repo]);
    }

    public function mount(): void
    {
        $this->currentStep = session('release.currentStep', 1);
        $this->issues = session('release.issues', []);
        $this->branches = session('release.branches', []);
        $this->matchedBranches = session('release.matchedBranches', []);
        $this->releaseName = session('release.releaseName', null);
        $this->releaseDate = session('release.releaseDate', null);
        $this->selectedRepo = session('release.selectedRepo', null);
        $this->releaseBranchFormatted = session('release.releaseBranchFormatted', null);
        $this->releaseStatus = session('release.releaseStatus', []);
        $this->releaseBranchExists = session('release.releaseBranchExists', false);
        $this->pullRequestResults = session('release.pullRequestResults', []);
        $this->unmatchedIssues = session('release.unmatchedIssues', []);
    }

    public function onJiraReleaseNameUpdated(string $releaseName, ?string $releaseDate = null): void
    {
        $this->releaseName = $releaseName;
        session(['release.releaseName' => $releaseName]);

        if ($releaseDate) {
            $this->releaseDate = $releaseDate;
            session(['release.releaseDate' => $releaseDate]);
        }
    }


    public function onJiraIssuesUpdated(array $issues, ReleaseWorkflowService $service): void
    {
        $service->handleJiraIssuesUpdated($this, $issues);
    }

    public function onJiraReleaseStatusUpdated(array $releaseStatus, ReleaseWorkflowService $service): void
    {
        $service->handleJiraReleaseStatusUpdated($this, $releaseStatus);
    }

    public function onGithubBranchesUpdated(array $branches, ReleaseWorkflowService $service): void
    {
        $service->handleGithubBranchesUpdated($this, $branches);
    }

    public function createReleaseBranch(ReleaseWorkflowService $service): void
    {
        $service->createReleaseBranch($this);
    }

    public function createMergeProposals(ReleaseWorkflowService $service): void
    {
        $service->createMergeProposals($this);
    }

    public function goToStep(int $step, ReleaseWorkflowService $service = null, GitHubService $servicee): void
    {
        $this->currentStep = $step;
        session(['release.currentStep' => $this->currentStep]);

        if ($step === 5 && $service && $this->selectedRepo) {
            if ($this->releaseBranchName) {
                $this->releaseBranchExists = $service->releaseBranchExists($this->selectedRepo, $this->releaseBranchName);
                $this->releaseBranchFormatted = $this->releaseBranchName;
            } else {
                $this->releaseBranchExists = false;
                $this->releaseBranchFormatted = null;
            }

            session([
                'release.releaseBranchExists' => $this->releaseBranchExists,
                'release.releaseBranchFormatted' => $this->releaseBranchFormatted,
            ]);
        }
    }

    public function resetWorkflow(): void
    {
        session()->forget('release');
        $this->reset([
            'currentStep',
            'issues',
            'branches',
            'matchedBranches',
            'releaseName',
            'releaseDate',
            'selectedRepo',
            'releaseStatus',
            'releaseBranchExists',
            'releaseBranchFormatted',
            'pullRequestResults',
        ]);
    }

    public function getReleaseBranchNameProperty()
    {
        if ($this->releaseDate) {
            $datum = $this->releaseDate;
            return 'release-tool/' . $datum;
        } elseif (preg_match('/\d{4}-\d{2}-\d{2}/', $this->releaseName, $matches)) {
            $datum = $matches[0];
            return 'release-tool/' . $datum;
        } else {
            return null;
        }
    }

    public function render()
    {
        $jiraBaseUrl = \App\Manager\IntegrationManager::current()->jira_url ?? 'https://happyhorizon.atlassian.net';
        $GitHubBaseUrl = \App\Manager\IntegrationManager::current()->github_url ?? 'https://github.com/';

        return view('livewire.release-workflow-component', [
            'jiraBaseUrl' => $jiraBaseUrl,
            'GitHubBaseUrl' => $GitHubBaseUrl,
        ]);
    }
}
