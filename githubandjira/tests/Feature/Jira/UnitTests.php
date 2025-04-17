<?php

namespace Jira;

use App\Livewire\JiraService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class UnitTests extends TestCase
{

    public function test_it_fetches_project_and_issues_from_jira()
    {
        // Step 1: fake beide Jira responses
        Http::fake([
            'https://your-jira-domain/rest/api/3/project/PROJ' => Http::response([
                'id' => 1,
                'key' => 'PROJ'
            ]),
            'https://your-jira-domain/rest/api/3/search*' => Http::response([
                'issues' => [
                    [
                        'key' => 'PROJ-101',
                        'fields' => [
                            'summary' => 'Login werkt niet',
                            'customfield_branch' => 'feature/PROJ-101-login'
                        ]
                    ]
                ]
            ])
        ]);

        // Stap 2: fake de config (anders krijg je echte calls of errors)
        config([
            'jira.email' => 'test@example.com',
            'jira.token' => 'fake-token',
            'jira.url' => 'your-jira-domain',
        ]);

        // Stap 3: gebruik de echte service
        $jira = new JiraService();

        $project = $jira->fetchProject('PROJ');
        $issues = $jira->fetchIssues('PROJ');

        // Stap 4: asserties
        $this->assertEquals(['id' => 1, 'key' => 'PROJ'], $project);
        $this->assertCount(1, $issues);
        $this->assertEquals('PROJ-101', $issues[0]['key']);
    }


    // Unit Test
    public function test_it_fetches_release_issues()
    {
        // Mock Jira version list
        Http::fake([
            'https://your-jira-domain/rest/api/3/project/PROJ/versions' => Http::response([
                ['id' => 1, 'name' => 'Release 1.0']
            ]),
            'https://your-jira-domain/rest/api/3/search*' => Http::response([
                'issues' => [
                    [
                        'key' => 'PROJ-301',
                        'fields' => [
                            'summary' => 'Bugfix',
                            'status' => ['name' => 'To Do'],
                            'issuetype' => ['name' => 'Bug'],
                        ]
                    ]
                ]
            ])
        ]);

        config([
            'jira.email' => 'test@example.com',
            'jira.token' => 'test-token',
            'jira.url' => 'your-jira-domain',
        ]);

        $jira = new JiraService();

        $issues = $jira->fetchReleaseIssues('PROJ', 'Release 1.0');

        $this->assertCount(1, $issues);
        $this->assertEquals('PROJ-301', $issues[0]['key']);
    }


}
