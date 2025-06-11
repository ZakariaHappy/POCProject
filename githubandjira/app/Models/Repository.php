<?php

namespace App\Models;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use JsonSerializable;
use Livewire\Wireable;

class Repository implements JsonSerializable, Arrayable, Jsonable, Wireable
{
    public function __construct(
        private string $name,
        private string $fullName,
        private bool $private,
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getFullName(): string
    {
        return $this->fullName;
    }

    public function setFullName(string $fullName): void
    {
        $this->fullName = $fullName;
    }

    public function isPrivate(): bool
    {
        return $this->private;
    }

    public function setPrivate(bool $private): void
    {
        $this->private = $private;
    }

    public function toArray()
    {
        return [
            'name' => $this->name,
            'fullName' => $this->fullName,
            'private' => $this->private,
        ];
    }

    public function toJson($options = 0): string
    {
        return json_encode($this->toArray(), $options);
    }

    public function toLivewire()
    {
        return $this->toArray();
    }

    public static function fromLivewire($value)
    {
        return new static(
            name: $value['name'],
            fullName: $value['fullName'],
            private: $value['private'],
        );
    }

    public function jsonSerialize(): array
    {
        return  $this->toArray();
    }
}
