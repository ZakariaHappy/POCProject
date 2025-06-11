<?php

namespace App\Livewire;

use App\Repositories\GitHubRepository;
use App\Models\Repository;
use App\Services\BranchMatcher;
use Livewire\Component;
use Livewire\Livewire;

class GithubSectionComponent extends Component
{
    public string $githubRepoName = '';
    public ?Repository $repository = null;
    public array $branches = [];
    public ?string $selectedRepo = null;
    public ?string $error = null;
    public array $issues = [];


    protected $listeners = [
//        'jiraIssuesUpdated' => 'updateIssues',
        'githubBranchesUpdated' => 'updateBranches',
//        'jiraReleaseNameUpdated' => 'setReleaseName',
        'setGithubRepo' => 'setSelectedRepo',
    ];

    public function updateIssues(array $issues): void
    {
        $this->issues = array_map(
            fn($i) => $i instanceof \App\Models\Issue ? $i->toArray() : app(\App\Factories\IssueFactory::class)->make($i)->toArray(),
            $issues
        );
    }

    public function showRepository(): void
    {
        try {
            $this->repository = app(GitHubRepository::class)
                ->getRepository($this->githubRepoName);

            // Reset
            $this->branches = [];
            $this->selectedRepo = null;
            $this->error = null;
        } catch (\Exception $e) {
            $this->repository = null;
            $this->error = $e->getMessage();
        }
    }

    public function getBranches(string $fullRepoName): void
    {
        logger('GithubSectionComponent::$issues:', $this->issues);
        if (empty($this->issues)) {
            $this->error = 'Er zijn nog geen Jira-issues opgehaald. Zoek eerst in Jira voordat je branches kunt bekijken.';
            $this->branches = [];
            return;
        }

        try {
            $jiraIssues = collect($this->issues)->map(
                fn($i) => $i instanceof \App\Models\Issue
                    ? $i
                    : app(\App\Factories\IssueFactory::class)->make($i)
            );
            $this->selectedRepo = $fullRepoName;
            $this->branches = app(GitHubRepository::class)
                ->getRelevantBranches($jiraIssues, $fullRepoName)
                ->map(fn($branch) => $branch->toArray())
                ->all();
            $this->dispatch('githubBranchesUpdated', $this->branches);
            $this->dispatch('setGithubRepo', $fullRepoName);

            $this->error = null;
        } catch (\Exception $e) {
            $this->branches = [];
            $this->error = $e->getMessage();
        }
    }


    public function render()
    {
        return view('livewire.github-section-component');
    }
}
