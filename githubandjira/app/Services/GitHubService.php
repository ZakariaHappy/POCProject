<?php

namespace App\Services;

use App\Clients\GitHubClient;
use App\Repositories\GitHubRepository;
use App\Factories\BranchFactory;
use Illuminate\Support\Collection;

class GitHubService
{
    public function __construct(
        protected GitHubRepository $gitHubRepo,
        protected BranchFactory $branchFactory,
        protected GitHubClient $githubClient,
    ) {
    }

    /**
     * Haal alle branches op voor een repo.
     */
    public function fetchBranches(string $fullRepoName): Collection
    {
        return $this->gitHubRepo->getBranches($fullRepoName);
    }

    /**
     * Maak een release branch aan vanuit main.
     */
    public function createReleaseBranch(string $releaseName, string $fullRepoName): void
    {
        $this->gitHubRepo->createReleaseBranch($releaseName, $fullRepoName);
    }

    public function formatBranchName(string $name): string
    {
        return $this->gitHubRepo->formatBranchName($name);
    }

    public function branchExists(string $fullRepoName, string $releaseName): bool
    {
        $branchName = $this->gitHubRepo->formatBranchName($releaseName);

        return $this->gitHubRepo->branchExists($fullRepoName, $branchName);
    }
}
