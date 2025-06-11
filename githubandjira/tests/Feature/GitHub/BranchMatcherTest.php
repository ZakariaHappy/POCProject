<?php

namespace Tests\Unit\Services;

use App\Models\Branch;
use App\Models\Issue;
use App\Services\BranchMatcher;
use PHPUnit\Framework\TestCase;

class BranchMatcherTest extends TestCase
{
    public function test_it_matches_branches_to_issues_on_key()
    {
        $matcher = new BranchMatcher();

        $issues = [
            new Issue('ABC-123', '', 'Onbekend', 'Onbekend'),
            new Issue('XYZ-999', '', 'Onbekend', 'Onbekend'),
        ];

        $branches = [
            new Branch('feature/ABC-123-login'),
            new Branch('hotfix/UNRELATED'),
        ];

        $result = $matcher->match($issues, $branches);

        $this->assertCount(1, $result);
        $this->assertEquals('ABC-123', $result[0]->issue->getKey());
        $this->assertEquals('feature/ABC-123-login', $result[0]->branch->getName()); // Of branch property
        $this->assertEquals('ABC-123', $result[0]->matchedOn); // of $result[0]->matchedOn, afhankelijk van property spelling

    }

    public function test_it_matches_on_linked_issue_keys_too()
    {
        $matcher = new BranchMatcher();

        $issues = [
            new Issue(
                'MAIN-001',
                '',
                'Onbekend',
                'Onbekend',
                null,
                [
                    ['inwardIssue' => ['key' => 'LINK-777']]
                ]
            )
        ];

        $branches = [
            new Branch('feature/LINK-777-abc'),
        ];

        $result = $matcher->match($issues, $branches);

        $this->assertCount(1, $result);
        $this->assertEquals('MAIN-001', $result[0]->issue->getKey());
        $this->assertEquals('feature/LINK-777-abc', $result[0]->branch->getName());
        $this->assertEquals('LINK-777', $result[0]->matchedOn);
    }

    public function test_it_returns_empty_when_no_matches()
    {
        $matcher = new BranchMatcher();

        $issues = [
            new Issue('ABC-123', '', 'Onbekend', 'Onbekend')
        ];

        $branches = [
            new Branch('feature/NO_MATCH_HERE'),
        ];

        $result = $matcher->match($issues, $branches);

        $this->assertEmpty($result);
    }

    public function test_it_returns_unmatched_issues_when_no_branch_matches()
    {
        $matcher = new BranchMatcher();

        $issues = [
            new Issue('ABC-123', '', 'Onbekend', 'Onbekend'),
            new Issue('XYZ-999', '', 'Onbekend', 'Onbekend'),
        ];

        $branches = [
            new Branch('feature/ABC-123-login'), // alleen deze matcht
        ];

        $matches = $matcher->match($issues, $branches);

        // Nu vinden we de unmatched issues door subtractie
        $matchedKeys = array_map(fn($m) => $m->issue->getKey(), $matches);
        $unmatched = array_filter($issues, fn($issue) => !in_array($issue->getKey(), $matchedKeys));

        $this->assertCount(1, $unmatched);
        $this->assertEquals('XYZ-999', array_values($unmatched)[0]->getKey());
    }

    public function test_match_branch_helper_returns_only_this_branch_matches()
    {
        $matcher = new BranchMatcher();
        $reflection = new \ReflectionClass($matcher);
        $method = $reflection->getMethod('matchBranch');
        $method->setAccessible(true);

        $issues = [
            new Issue('ABC-123', '', 'Onbekend', 'Onbekend'),
            new Issue('XYZ-999', '', 'Onbekend', 'Onbekend'),
        ];

        $branch = new Branch('feature/ABC-123-login');

        $result = $method->invokeArgs($matcher, [$branch, $issues]);

        $this->assertCount(1, $result);
        $this->assertEquals('ABC-123', $result[0]->matchedOn);
        $this->assertEquals('feature/ABC-123-login', $result[0]->branch->getName());
        $this->assertEquals('ABC-123', $result[0]->issue->getKey());
    }


}
