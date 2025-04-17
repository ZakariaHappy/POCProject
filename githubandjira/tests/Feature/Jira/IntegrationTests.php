<?php

namespace Jira;

use App\Livewire\JiraService;
use Tests\TestCase;

class IntegrationTests extends TestCase
{
    public function test_it_can_fetch_a_project_from_real_jira()
    {
        {
            $projectKey = 'SOL'; // 👈 jouw bestaande project key
            $expectedProjectName = 'Solar'; // 👈 optioneel: zet hier de naam zoals je 'm kent in Jira

            config([
                'jira.email' => env('JIRA_EMAIL'),
                'jira.token' => env('JIRA_TOKEN'),
                'jira.url' => env('JIRA_BASE_URL'), // bv. your-domain.atlassian.net
            ]);

            $jira = new JiraService();
            $project = $jira->fetchProject($projectKey);

            $this->assertNotNull($project, 'Project response is null – check je credentials & projectKey');
            $this->assertEquals($projectKey, $project['key'], 'Project key komt niet overeen');

            // Optioneel: controleer ook naam
            $this->assertEquals($expectedProjectName, $project['name'], 'Project naam komt niet overeen');
        }
    }

}
