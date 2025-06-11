<?php

namespace App\Factories;

use App\Models\PullRequest;

class PullRequestFactory extends BaseFactory
{
    public function make(array $data): PullRequest
    {
        $issue = $data['issue'] ?? [];

        if (is_string($issue)) {
            $issue = [
                [
                    'key' => $issue,
                    'summary' => '',
                ]
            ];
        }

        return new PullRequest(
            $data['branch'],
            $issue,
            $data['url'] ?? null,
            $data['mergeable'] ?? null,
            $data['error'] ?? null
        );
    }
}
