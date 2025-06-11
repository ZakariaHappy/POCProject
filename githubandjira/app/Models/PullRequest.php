<?php

namespace App\Models;

use Livewire\Wireable;

class PullRequest implements Wireable
{
    public function __construct(
        public string $branch,
        public array $issue,
        public ?string $url = null,
        public ?bool $mergeable = null,
        public ?string $error = null,
    ) {
    }



    public function toLivewire()
    {
        return get_object_vars($this);
    }

    public static function fromLivewire($value)
    {
        return new static(
            $value['branch'],
            $value['issue'],
            $value['url'] ?? null,
            $value['mergeable'] ?? null,
            $value['error'] ?? null,
        );
    }

    public function hasConflict(): bool
    {
        return $this->mergeable === false;
    }

    public function isPending(): bool
    {
        return $this->mergeable === null;
    }

    public function toArray(): array
    {
        return [
            'branch' => $this->branch,
            'issue' => $this->issue,
            'url' => $this->url,
            'mergeable' => $this->mergeable,
            'error' => $this->error,
        ];
    }

    public function withIssues(array $issues): static
    {
        $this->issue = $issues;
        return $this;
    }
}
