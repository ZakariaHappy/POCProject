<?php

namespace GitHub;

use App\Livewire\GitHubService;
use App\Livewire\ProjectSelection;
use Livewire\Livewire;
use Tests\TestCase;

class IntegrationTests extends TestCase
{

    public function test_it_can_fetch_a_branch_from_github()
    {
        $expectedBranch = 'ZSTAGE-1-fix';
        $repoName = config('github.repo');
        $token = config('github.token');

        $github = new GitHubService();

        // 1. Haal branches op via je service
        $branches = $github->fetchBranches($repoName);

        // 2. Extract branch names
        $branchNames = array_column($branches, 'name');

        // 3. Debugging (optioneel)
        dump($branchNames);

        // 4. Assertie
        $this->assertContains($expectedBranch, $branchNames, "Branch '{$expectedBranch}' niet gevonden.");
    }




    public function test_it_can_fetch_github_repositories()
    {
        config([
            'github.token' => env('GITHUB_TOKEN'),
        ]);

        $github = new GitHubService();

        $repos = $github->fetchRepositories(''); // of 'test-repo' als je zoekt
        $this->assertIsArray($repos);
        $this->assertNotEmpty($repos, 'Geen repositories opgehaald — check je token of permissies.');

        dump($repos);

        // Optioneel: check specifieke repo naam
        $repoNames = array_column($repos, 'name');
        $this->assertContains('TestingPoC', $repoNames);
    }


    public function test_it_matches_branches_to_issues()
    {
        $component = Livewire::test(ProjectSelection::class);

        $issues = [
            [
                'key' => 'PROJ-101',
                'fields' => [
                    'summary' => 'Fix login issue',
                    'issuetype' => ['name' => 'Bug'],
                    'status' => ['name' => 'To Do']
                ]
            ]
        ];


        $branches = [
            ['name' => 'feature/PROJ-101-login'],
            ['name' => 'bugfix/PROJ-999-other'],
            ['name' => 'hotfix/PROJ-202-urgent'],
        ];

        $component
            ->set('issues', $issues)
            ->set('branches', $branches)
            ->call('matchBranchesToIssues')
            ->assertSet('matchedBranches', [
                [
                    'issue' => [
                        'key' => 'PROJ-101',
                        'fields' => [
                            'summary' => 'Fix login issue',
                            'issuetype' => ['name' => 'Bug'],
                            'status' => ['name' => 'To Do']
                        ]
                    ],
                    'branch' => 'feature/PROJ-101-login',
                ]
            ]);
    }


}
