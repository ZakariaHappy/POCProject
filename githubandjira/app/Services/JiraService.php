<?php

namespace App\Services;

use App\Repositories\IssueRepository;
use App\Models\Issue;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class JiraService
{
    public function __construct(
        protected IssueRepository $issueRepo,
    ) {
    }

    public function fetchIssuesWithDetails(string $projectKey, string $releaseName): Collection
    {
        $mainIssues = $this->issueRepo->getIssuesByReleaseName($projectKey, $releaseName);

        return $mainIssues->map(function (Issue $issue) {
            $issue->setSubtasks($this->issueRepo->getSubtasks($issue->getKey()));
            $issue->setLinkedIssues($this->issueRepo->getLinkedIssues($issue->getIssueLinks()));
            return $issue;
        });
    }

    public function classifyStatus(Collection $mainIssues): array
    {
        $accepted = collect();
        $notAccepted = collect();
        $seenAccepted = [];
        $seenNotAccepted = [];

        $mainIssues->each(function ($issue) use (&$accepted, &$notAccepted, &$seenAccepted, &$seenNotAccepted) {
            $this->collectIssueByStatus($issue, $accepted, $notAccepted, $seenAccepted, $seenNotAccepted);
            $this->collectNestedIssues($issue->getSubtasks(), $accepted, $notAccepted, $seenAccepted, $seenNotAccepted);
            $this->collectLinkedIssues($issue, $accepted, $notAccepted, $seenAccepted, $seenNotAccepted);
        });

        return [
            'Accepted for Deployment' => $accepted,
            'notAccepted'             => $notAccepted,
        ];
    }

    private function collectIssueByStatus($issue, Collection &$accepted, Collection &$notAccepted, array &$seenAccepted, array &$seenNotAccepted): void
    {
        $key = $issue->getKey();
        $status = $issue->getStatus();
        $completedStatuses = [
            'Accepted for Deployment',
            'Accepted (migrated)',
            'Done',
        ];

        if (in_array($status, $completedStatuses, true)) {
            if (!isset($seenAccepted[$key])) {
                $accepted->push($issue);
                $seenAccepted[$key] = true;
            }
        } else {
            if (!isset($seenNotAccepted[$key])) {
                error_log("Openstaand: [{$issue->getKey()}] STATUS: \"{$issue->getStatus()}\"");
                $notAccepted->push($issue);
                $seenNotAccepted[$key] = true;
            }
        }
    }
    private function collectNestedIssues($issues, Collection &$accepted, Collection &$notAccepted, array &$seenAccepted, array &$seenNotAccepted): void
    {
        collect($issues)->each(function ($issue) use (&$accepted, &$notAccepted, &$seenAccepted, &$seenNotAccepted) {
            $this->collectIssueByStatus($issue, $accepted, $notAccepted, $seenAccepted, $seenNotAccepted);
        });
    }
    private function collectLinkedIssues($issue, Collection &$accepted, Collection &$notAccepted, array &$seenAccepted, array &$seenNotAccepted): void
    {
        $issueLinks = method_exists($issue, 'getIssueLinks') ? $issue->getIssueLinks() : [];
        if (!empty($issueLinks)) {
            $linkedIssues = $this->issueRepo->getLinkedIssues($issueLinks);
            $linkedIssues->each(function ($linkedIssue) use (&$accepted, &$notAccepted, &$seenAccepted, &$seenNotAccepted) {
                $linkedIssue->isLinkedIssue = true;
                $this->collectIssueByStatus($linkedIssue, $accepted, $notAccepted, $seenAccepted, $seenNotAccepted);
            });
        }
    }
}
