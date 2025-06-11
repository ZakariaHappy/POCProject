<?php

namespace App\Services;

class ReleaseWorkflowSessionService
{
    private string $prefix = 'release.';

    public function get(string $key, $default = null)
    {
        return session($this->prefix . $key, $default);
    }

    public function set(string $key, $value): void
    {
        session([$this->prefix . $key => $value]);
    }

    public function forgetAll(): void
    {
        foreach (
            [
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
                 ] as $key
        ) {
            session()->forget($this->prefix . $key);
        }
    }
}
