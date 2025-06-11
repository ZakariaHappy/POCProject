<?php

namespace App\Factories;

use App\Models\Issue;

class IssueFactory extends BaseFactory
{
    public function make($data): Issue
    {
        // If already an Issue instance, just return
        if ($data instanceof Issue) {
            return $data;
        }

        // If fields present, normalize data
        if (isset($data['fields'])) {
            $fields = $data['fields'];

            $assignee = $fields['assignee'] ?? null;
            if ($assignee) {
                $assignee = $assignee['displayName']
                    ?? $assignee['name']
                    ?? $assignee['emailAddress']
                    ?? null;
            }

            $uniform = [
                'key'         => $data['key'] ?? null,
                'summary'     => $fields['summary'] ?? '',
                'status'      => $fields['status']['name'] ?? 'Onbekend',
                'type'        => $fields['issuetype']['name'] ?? 'Onbekend',
                'parentKey'   => $fields['parent']['key'] ?? null,
                'issuelinks'  => $fields['issuelinks'] ?? [],
                'assignee'    => $assignee,
                'subtasks'    => $data['subtasks'] ?? [],
                'linkedIssues' => $data['linkedIssues'] ?? [],
            ];
        } else {
            $uniform = $data;
        }

        return $this->makeFromArray($uniform);
    }

    public function makeFromArray(array $data): Issue
    {
        return app(Issue::class, [
            'key'          => $data['key'] ?? null,
            'summary'      => $data['summary'] ?? '',
            'status'       => $data['status'] ?? 'Onbekend',
            'type'         => $data['type'] ?? 'Onbekend',
            'parentKey'    => $data['parentKey'] ?? null,
            'issuelinks'   => $data['issuelinks'] ?? [],
            'assignee'     => $data['assignee'] ?? null,
            'subtasks'     => !empty($data['subtasks'])
                ? collect($data['subtasks'])->map([Issue::class, 'fromArray'])
                : collect(),
            'linkedIssues' => !empty($data['linkedIssues'])
                ? collect($data['linkedIssues'])->map([Issue::class, 'fromArray'])
                : collect(),
        ]);
    }
}
