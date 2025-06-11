<?php

namespace App\Services;

use App\Models\MatchResult;
use App\Models\PullRequest;
use App\Repositories\GitHubRepository;
use Illuminate\Support\Collection;

class MergeProposal
{
    public function __construct(private GitHubRepository $githubRepo)
    {
    }


    public function generate(Collection $matchedBranches, string $releaseBranchName, string $fullRepoName): Collection
    {
        $releaseBranch = $this->formatBranchName($releaseBranchName);

        if (!$this->githubRepo->branchExists($fullRepoName, $releaseBranch)) {
            return $this->errorsForMissingReleaseBranch($matchedBranches, $releaseBranch);
        }

        return $this->generatePullRequestsPerBranch($matchedBranches, $fullRepoName, $releaseBranch);
    }


    protected function errorsForMissingReleaseBranch(Collection $matchedBranches, string $releaseBranch): Collection
    {
        return $matchedBranches->map(function ($match) use ($releaseBranch) {
            return new PullRequest(
                branch: $match['branch']['name'],
                issue: [
                    [
                        'key' => $match['issue']['key'],
                        'summary' => $match['issue']['summary'] ?? '',
                    ]
                ],
                url: null,
                mergeable: null,
                error: "Release branch '{$releaseBranch}' bestaat niet. Maak deze eerst aan voordat je pull requests aanmaakt. Doe dit in de vorige stap!"
            );
        });
    }

    protected function generatePullRequestsPerBranch(Collection $matchedBranches, string $fullRepoName, string $releaseBranch): Collection
    {
        return $matchedBranches
            ->groupBy(fn($match) => $match['branch']['name'])
            ->map(fn($matches, $branchName) =>
            $this->generatePullRequestForBranch($matches, $branchName, $fullRepoName, $releaseBranch))->values();
    }

    protected function generatePullRequestForBranch(Collection $matches, string $branchName, string $fullRepoName, string $releaseBranch): PullRequest
    {
        $issueData = $this->extractIssueData($matches);
        $prBody = $this->makePrBody($issueData);

        try {
            return $this->githubRepo->createMergeProposal(
                $fullRepoName,
                $branchName,
                $releaseBranch,
                $prBody
            )->withIssues($issueData);
        } catch (\Exception $e) {
            return new PullRequest(
                branch: $branchName,
                issue: $issueData,
                url: null,
                mergeable: null,
                error: $e->getMessage()
            );
        }
    }

    protected function extractIssueData(Collection $matches): array
    {
        return $matches->map(fn($match) => [
            'key' => $match['issue']['key'],
            'summary' => $match['issue']['summary'] ?? '',
        ])->unique('key')->values()->all();
    }

    protected function makePrBody(array $issueData): string
    {
        $issueSummary = implode(', ', array_column($issueData, 'key'));

        return "Automatisch mergevoorstel voor Jira issues: {$issueSummary}.";
    }

    protected function formatBranchName(string $name): string
    {
        return preg_replace('/[^A-Za-z0-9_\-]/', '-', $name);
    }
}
