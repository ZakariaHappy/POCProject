<?php

namespace App\Factories;

use App\Models\Branch;

class BranchFactory extends BaseFactory
{
    public function make(array $data): Branch
    {
        return new Branch($data['name']);
    }
}
