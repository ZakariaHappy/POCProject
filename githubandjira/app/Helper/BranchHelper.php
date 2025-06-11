<?php

namespace App\Helper;

class BranchHelper
{
    public static function format(string $name): string
    {
        $name = trim($name);
        $name = str_replace(' ', '-', $name);
        $name = preg_replace('/[^A-Za-z0-9_\-]/', '-', $name);
        $name = preg_replace('/-+/', '-', $name);

        return strtolower($name);
    }

    public static function extractIssueKeyFromBranchName(string $branchName): ?string
    {
        if (preg_match('/([A-Z]+-\d+)/', $branchName, $matches)) {
            return $matches[1];
        }

        return null;
    }

}
