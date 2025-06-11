<?php

namespace App\Factories;

use App\Models\Repository;

class GitHubFactory extends BaseFactory
{
    public function make(array $data): Repository
    {
        return new Repository(
            $data['name'],
            $data['full_name'],
            $data['private']
        );
    }
}
