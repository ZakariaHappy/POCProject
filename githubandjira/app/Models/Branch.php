<?php

namespace App\Models;

use JsonSerializable;
use Livewire\Wireable;

class Branch implements JsonSerializable, Wireable
{
    public function __construct(public string $name)
    {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public static function fromArray(array $data): static
    {
        return new static($data['name']);
    }

    public function toArray(): array
    {
        return ['name' => $this->name];
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    public function toLivewire(): array
    {
        return $this->toArray();
    }

    public static function fromLivewire($value): static
    {
        return static::fromArray($value);
    }

    public function matchesKey(string $key): bool
    {
        return str_contains(strtoupper($this->getName()), strtoupper($key));
    }
}
