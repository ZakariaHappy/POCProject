<?php

namespace App\Repositories;

use App\Clients\GitHubClient;
use App\Factories\BranchFactory;
use App\Factories\GitHubFactory;
use App\Factories\PullRequestFactory;
use App\Models\Branch;
use App\Models\Repository;
use App\Models\PullRequest;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

class GitHubRepository
{
    public function __construct(
        protected GitHubClient $client,
        protected GitHubFactory $factory,
        protected BranchFactory $branchFactory,
        protected PullRequestFactory $pullRequestFactory,
    ) {
    }

    public function formatBranchName(string $name): string
    {
        $name = trim($name);
        $name = str_replace(' ', '-', $name);
        $name = preg_replace('/[^A-Za-z0-9_\-]/', '-', $name);
        $name = preg_replace('/-+/', '-', $name);

        return $name;
    }


    /**
     * Haal repo info op (als model)
     */
    public function getRepository(string $fullRepoName): Repository
    {
        $fullRepo = $this->getFullRepoName($fullRepoName);
        $repoData = $this->client->get("/repos/{$fullRepo}");

        return $this->factory->make($repoData);
    }

    /**
     * Haal alle branches op (als Collection van Branch modellen)
     */
    public function getBranches(string $fullRepoName): Collection
    {
        $allBranches = collect();
        $page = 1;

        do {
            $branchesData = $this->client->get("/repos/{$fullRepoName}/branches", [
                'per_page' => 100,
                'page' => $page,
            ]);
            $collection = $this->branchFactory->makeMultiple($branchesData);
            $allBranches = $allBranches->merge($collection);
            $page++;
        } while (count($branchesData) === 100);

        logger('Aantal opgehaalde branches: ' . $allBranches->count());

        return $allBranches;
    }


    /**
     * Haal relevante branches op (branches waarvan de naam een Jira issue key bevat)
     */
    public function getRelevantBranches(Collection $jiraIssues, string $fullRepoName): Collection
    {
        $branches = $this->getBranches($fullRepoName);
        $issueKeys = $jiraIssues->map(fn($issue) => $issue->getKey());

        return $branches->filter(function (Branch $branch) use ($issueKeys) {
            foreach ($issueKeys as $key) {
                if (str_contains($branch->getName(), $key)) {

                    return true;
                }
            }

            return false;
        })->values(); // values() voor nette indexering
    }

    /**
     * Haal de SHA van main branch op
     */
    public function getLatestMainSha(string $fullRepoName): string
    {
        $fullRepo = $this->getFullRepoName($fullRepoName);
        $endpoint = "/repos/{$fullRepo}/git/refs/heads/main";
        $data = $this->client->get($endpoint);

        if (empty($data['object']['sha'])) {
            throw new \RuntimeException("Kon geen SHA vinden voor main branch van {$fullRepo}.");
        }

        return $data['object']['sha'];
    }

    /**
     * Maak een release branch aan
     */
    public function createReleaseBranch(string $releaseName, string $fullRepoName): void
    {
        $fullRepo = $this->getFullRepoName($fullRepoName);
        $sha = $this->getLatestMainSha($fullRepoName);
        $branch = preg_replace('/[^A-Za-z0-9_\-]/', '-', $releaseName);

        $endpoint = "/repos/{$fullRepo}/git/refs";
        $this->client->post($endpoint, [
            'ref' => "refs/heads/{$branch}",
            'sha' => $sha,
        ]);
    }

    /**
     * Maak een merge proposal aan (PR)
     */
    public function createMergeProposal(
        string $fullRepoName,
        string $branchName,
        string $releaseName,
        ?string $body = null,
        ?string $title = null
    ): PullRequest {
        $fullRepo = $this->getFullRepoName($fullRepoName);
        $releaseBranch = $this->formatBranchName($releaseName);

        // Gebruik meegegeven titel/body, of de standaard
        $prTitle = $title ?? "Merge {$branchName} naar {$releaseBranch}";
        $prBody = $body ?? "Automatisch mergevoorstel voor Jira issue(s) {$branchName}.";

        $pullRequest = $this->client->post("/repos/{$fullRepo}/pulls", [
            'title' => $prTitle,
            'head' => $branchName,
            'base' => $releaseBranch,
            'body' => $prBody,
        ]);

        $prNumber = $pullRequest['number'];
        $prUrl = $pullRequest['html_url'];

        sleep(2);

        try {
            $details = $this->client->get("/repos/{$fullRepo}/pulls/{$prNumber}");
            $mergeable = $details['mergeable'] ?? null;
        } catch (\Exception $e) {
            $mergeable = null;
        }

        return $this->pullRequestFactory->make([
            'url' => $prUrl,
            'branch' => $branchName,
            'issue' => null, // Laat issue leeg, want je vult straks in je service alle issues
            'mergeable' => $mergeable,
        ]);
    }


    /**
     * Helper om altijd owner/repo string te krijgen
     */
    protected function getFullRepoName(string $repo): string
    {
        if (str_contains($repo, '/')) {
            return $repo;
        }

        $username = $this->client->getOwner();

        if (!$username) {
            throw new \RuntimeException("No Repository username found.");
        }

        return "{$username}/{$repo}";
    }

    public function branchExists(string $fullRepoName, string $branchName): bool
    {
        try {
            $branch = $this->client->get("/repos/{$fullRepoName}/branches/{$branchName}");
            return !empty($branch);
        } catch (\Exception $e) {
            if (str_contains($e->getMessage(), '404')) {
                return false;
            }
            throw $e;
        }
    }
}
