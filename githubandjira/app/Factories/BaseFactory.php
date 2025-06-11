<?php

namespace App\Factories;

use Illuminate\Support\Collection;

abstract class BaseFactory
{
    abstract public function make(array $data);

    public function makeMultiple(array $data): Collection
    {
        return collect($data)->map(fn($data) => $this->make($data));
    }
}
