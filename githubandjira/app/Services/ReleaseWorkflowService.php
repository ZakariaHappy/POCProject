<?php

namespace App\Services;

use App\Repositories\GitHubRepository;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use App\Services\JiraService;
use App\Services\GitHubService;
use App\Services\BranchMatcher;
use App\Services\MergeProposal;
use App\Factories\IssueFactory;
use App\Factories\BranchFactory;
use Illuminate\Support\Collection;


class ReleaseWorkflowService
{
    public function __construct(
        protected JiraService $jiraService,
        protected GitHubService $gitHubService,
        protected BranchMatcher $matcher,
        protected MergeProposal $mergeProposal,
        protected IssueFactory $issueFactory,
        protected BranchFactory $branchFactory,
        protected GitHubRepository $githubRepository,
    ) {
    }

    /**
     * Haal en hydrateer alles voor een release (combineert JIRA & GitHub)
     */
    public function getReleaseOverview(string $projectKey, string $releaseName, string $fullRepoName): array
    {
        $issues = $this->jiraService->fetchIssuesWithDetails($projectKey, $releaseName); // Collection
        $releaseStatus = $this->jiraService->classifyStatus($issues); // array van Collections?
        $branches = $this->gitHubService->fetchBranches($fullRepoName); // Collection

        return [
            'issues'        => $issues->toArray(),
            'releaseStatus' => array_map(fn($status) => $status->toArray(), $releaseStatus), // als het collections zijn
            'branches'      => $branches->toArray(),
        ];
    }



    public function handleJiraIssuesUpdated(Component $component, array $issues): void
    {
        $hydrated = $this->issueFactory->makeMultiple($issues); // Collection

        $component->issues = $hydrated->map(fn($i) => $i->toArray())->all();
        session(['release.issues' => $component->issues]); // 🧠 opslaan

        $status = $this->jiraService->classifyStatus($hydrated); // array van Collections

        $component->releaseStatus = [
            'notAccepted' => $status['notAccepted']->map(fn($i) => $i->toArray())->all(),
            'Accepted for Deployment' => $status['Accepted for Deployment']->map(fn($i) => $i->toArray())->all(),
        ];
        session(['release.releaseStatus' => $component->releaseStatus]); // 🧠 opslaan

        $this->tryMatching($component);
    }




    public function handleJiraReleaseStatusUpdated(Component $component, array $releaseStatus): void
    {
        $component->releaseStatus['notAccepted'] = $releaseStatus['notAccepted'] ?? [];
        $component->releaseStatus['Accepted for Deployment'] = $releaseStatus['Accepted for Deployment'] ?? [];
    }



    public function handleGithubBranchesUpdated(Component $component, array $branches): void
    {
        $component->branches = $this->branchFactory->makeMultiple($branches)
            ->map(fn($i) => $i->toArray())
            ->all();
        $this->tryMatching($component);
    }

    public function createReleaseBranch(Component $component): void
    {
        try {
            $releaseBranchName = $component->releaseBranchName;

            if (!$releaseBranchName) {
                session()->flash('error', 'Er is geen geldige releasedatum. Kan geen release branch aanmaken.');
                return;
            }

            $this->gitHubService->createReleaseBranch($releaseBranchName, $component->selectedRepo);
            session()->flash('jira_message', "Release branch '{$releaseBranchName}' is aangemaakt!");
        } catch (\Exception $e) {
            session()->flash('error', 'Er is een fout opgetreden!');
            session()->flash('error_details', $e->getMessage());
        }
    }

    public function createMergeProposals(Component $component): void
    {
        $component->pullRequestResults = $this->mergeProposal
            ->generate(
                collect($component->matchedBranches),
                $component->releaseBranchName,   // <--- DIT MOET HIER!
                $component->selectedRepo
            )
            ->map(fn($pr) => $pr->toArray())
            ->all();

        session()->flash('match_message', 'Alle mergevoorstellen zijn aangemaakt!');
        $component->dispatch('scroll-to-merge-results');
    }


    public function releaseBranchExists(string $repo, string $releaseName): bool
    {
        return $this->gitHubService->branchExists($repo, $releaseName);
    }


    private function tryMatching(Component $component): void
    {
        $hydratedIssues = $this->hydrateIssues($component->issues);
        $hydratedBranches = $this->hydrateBranches($component->branches);

        $matched = $this->generateMatches($hydratedIssues, $hydratedBranches);
        $component->matchedBranches = $matched;
        session(['release.matchedBranches' => $matched]);

        $unmatched = $this->determineUnmatchedIssues($hydratedIssues, $matched);
        $component->unmatchedIssues = $unmatched;
        session(['release.unmatchedIssues' => $unmatched]);
    }


    private function hydrateIssues(array $rawIssues): Collection
    {
        $all = $this->getAllMatchableIssues($rawIssues);
        return $this->issueFactory->makeMultiple($all);
    }

    private function hydrateBranches(array $rawBranches): Collection
    {
        return collect($rawBranches)
            ->map(fn($b) => $b instanceof \App\Models\Branch ? $b : $this->branchFactory->make($b))
            ->filter();
    }

    private function generateMatches(Collection $issues, Collection $branches): array
    {
        return $this->matcher->match($issues, $branches)
            ->map(fn($match) => $match->toArray())
            ->all();
    }

    protected function determineUnmatchedIssues(Collection $issues, array $matched): array
    {
        $matchedKeys = collect($matched)->pluck('issue.key')->all();

        return $issues
            ->filter(function ($issue) use ($matchedKeys) {
                $key = method_exists($issue, 'getKey') ? $issue->getKey() : $issue->key ?? null;
                $parentKey = method_exists($issue, 'getParentKey') ? $issue->getParentKey() : $issue->parentKey ?? null;

                return !in_array($key, $matchedKeys)
                    && !($parentKey && in_array($parentKey, $matchedKeys));
            })
            ->map(fn($issue) => $issue->toArray())
            ->values()
            ->all();
    }

    public function getAllMatchableIssues(array $issues): array
    {
        $all = [];
        foreach ($issues as $issue) {
            $all[] = $issue;

            // Subtasks
            if (!empty($issue['subtasks'])) {
                foreach ($issue['subtasks'] as $sub) {
                    $all[] = $sub;
                }
            }
            // Linked issues
            if (!empty($issue['linkedIssues'])) {
                foreach ($issue['linkedIssues'] as $linked) {
                    $all[] = $linked;
                }
            }
        }
        // Unieke keys (optioneel)
        $unique = [];
        foreach ($all as $item) {
            $unique[$item['key']] = $item;
        }

        return array_values($unique);
    }



}
