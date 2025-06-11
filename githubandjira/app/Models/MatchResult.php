<?php

namespace App\Models;

use Livewire\Wireable;

class MatchResult implements Wireable
{
    public function __construct(
        public readonly Issue $issue,
        public readonly Branch $branch,
        public readonly string $matchedOn
    ) {
    }

    public function toLivewire()
    {
        return [
            'issue' => $this->issue->toLivewire(),
            'branch' => $this->branch->toLivewire(),
            'matchedOn' => $this->matchedOn,
        ];
    }

    public static function fromLivewire($value)
    {
        return new static(
            Issue::fromLivewire($value['issue']),
            Branch::fromLivewire($value['branch']),
            $value['matchedOn']
        );
    }

    public function toArray(): array
    {
        return [
            'issue' => $this->issue->toArray(),
            'branch' => $this->branch->toArray(),
            'matchedOn' => $this->matchedOn,
        ];
    }
}
