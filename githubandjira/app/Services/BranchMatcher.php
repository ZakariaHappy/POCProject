<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\Issue;
use App\Models\MatchResult;
use Illuminate\Support\Collection;

class BranchMatcher
{
    /**
     * @param Collection<int, Issue>  $issues
     * @param Collection<int, Branch> $branches
     * @return Collection<int, MatchResult>
     */
    public function match(Collection $issues, Collection $branches): Collection
    {
        if ($issues->isEmpty() || $branches->isEmpty()) {
            return collect();
        }

        return $branches->flatMap(function (Branch $branch) use ($issues) {
            return $this->matchBranch($branch, $issues);
        })->values();
    }

    /**
     * @param Branch $branch
     * @param Collection<int, Issue> $issues
     * @return Collection<int, MatchResult>
     */
    private function matchBranch(Branch $branch, Collection $issues): Collection
    {
        return $issues->map(function (Issue $issue) use ($branch) {
            $keys = $issue->getAllKeys();
            $matchedKey = $this->findMatchingKey($branch, $keys);

            if ($matchedKey !== null) {
                return new MatchResult($issue, $branch, $matchedKey);
            }
            return null;
        })->filter()->values();
    }

    /**
     * @param Branch   $branch
     * @param string[] $keys
     * @return string|null
     */
    private function findMatchingKey(Branch $branch, array $keys): ?string
    {
        foreach ($keys as $key) {
            // Match bijv: feature/SPD-1234, bug/SPD-1234, SPD-1234-omschrijving, etc.
            if (preg_match('/\b' . preg_quote($key, '/') . '\b/i', $branch->name)) {
                return $key;
            }
        }

        return null;
    }
}
