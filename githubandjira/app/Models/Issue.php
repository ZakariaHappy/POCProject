<?php

namespace App\Models;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Support\Collection;
use JsonSerializable;
use Livewire\Wireable;

class Issue implements JsonSerializable, Arrayable, Jsonable, Wireable
{
    // Core properties
    private string $key;
    private string $summary;
    private string $status;
    private string $type;
    private ?string $parentKey;
    private array $issuelinks;
    private ?string $assignee;

    // Collections
    private Collection $subtasks;
    private Collection $linkedIssues;

    public function __construct(
        string $key,
        string $summary,
        string $status,
        string $type,
        ?string $parentKey = null,
        array $issuelinks = [],
        ?string $assignee = null,
        Collection $subtasks = null,
        Collection $linkedIssues = null,
    ) {
        $this->key = $key;
        $this->summary = $summary;
        $this->status = $status;
        $this->type = $type;
        $this->parentKey = $parentKey;
        $this->issuelinks = $issuelinks;
        $this->assignee = $assignee;
        $this->subtasks = $subtasks ?? collect();
        $this->linkedIssues = $linkedIssues ?? collect();
    }

    /** ---------- Factories ---------- */
    public static function fromArray(array $data): static
    {
        return new static(
            key: $data['key'],
            summary: $data['summary'] ?? '',
            status: $data['status'] ?? 'Onbekend',
            type: $data['type'] ?? 'Onbekend',
            parentKey: $data['parentKey'] ?? null,
            issuelinks: $data['issuelinks'] ?? [],
            assignee: $data['assignee'] ?? null,
            subtasks: !empty($data['subtasks'])
                ? collect($data['subtasks'])->map([static::class, 'fromArray'])
                : collect(),
            linkedIssues: !empty($data['linkedIssues'])
                ? collect($data['linkedIssues'])->map([static::class, 'fromArray'])
                : collect(),
        );
    }

    public static function fromLivewire($value): static
    {
        return static::fromArray($value);
    }

    /** ---------- Array/JSON/Livewire ---------- */
    public function toArray(): array
    {
        return [
            'key'          => $this->key,
            'summary'      => $this->summary,
            'status'       => $this->status,
            'type'         => $this->type,
            'parentKey'    => $this->parentKey,
            'issuelinks'   => $this->issuelinks,
            'assignee'     => $this->assignee,
            'subtasks'     => $this->subtasks->map(fn(Issue $sub) => $sub->toArray())->all(),
            'linkedIssues' => $this->linkedIssues->map(fn(Issue $issue) => $issue->toArray())->all(),
        ];
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    public function toJson($options = 0): string
    {
        return json_encode($this->toArray(), $options);
    }

    public function toLivewire(): array
    {
        return $this->toArray();
    }

    /** ---------- Linked/Related ---------- */
    public function getAllKeys(): array
    {
        $keys = [strtoupper($this->key)];
        foreach ($this->issuelinks as $link) {
            $linkedKey = $link['inwardIssue']['key'] ?? $link['outwardIssue']['key'] ?? null;
            if ($linkedKey) {
                $keys[] = strtoupper($linkedKey);
            }
        }
        return $keys;
    }

    public function isSubtask(): bool
    {
        return in_array($this->type, ['Subtask', 'Subtaak'], true);
    }

    /** ---------- Getters/Setters ---------- */
    public function getKey(): string
    {
        return $this->key;
    }
    public function setKey(string $key): void
    {
        $this->key = $key;
    }

    public function getSummary(): string
    {
        return $this->summary;
    }
    public function setSummary(string $summary): void
    {
        $this->summary = $summary;
    }

    public function getStatus(): string
    {
        return $this->status;
    }
    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    public function getType(): string
    {
        return $this->type;
    }
    public function setType(string $type): void
    {
        $this->type = $type;
    }

    public function getParentKey(): ?string
    {
        return $this->parentKey;
    }
    public function setParentKey(?string $parentKey): void
    {
        $this->parentKey = $parentKey;
    }

    public function getAssignee(): ?string
    {
        return $this->assignee;
    }
    public function setAssignee(?string $assignee): void
    {
        $this->assignee = $assignee;
    }

    public function getIssueLinks(): array
    {
        return $this->issuelinks;
    }
    public function setIssueLinks(array $issuelinks): void
    {
        $this->issuelinks = $issuelinks;
    }

    /** ---------- Subtasks/LinkedIssues ---------- */
    public function getSubtasks(): Collection
    {
        return $this->subtasks;
    }
    public function setSubtasks(Collection $subtasks): void
    {
        $this->subtasks = $subtasks;
    }
    public function addSubtask(Issue $issue): void
    {
        $this->subtasks[] = $issue;
    }

    public function getLinkedIssues(): Collection
    {
        return $this->linkedIssues;
    }
    public function setLinkedIssues(Collection $linkedIssues): void
    {
        $this->linkedIssues = $linkedIssues;
    }
    public function addLinkedIssue(Issue $issue): void
    {
        $this->linkedIssues[] = $issue;
    }
}
