<?php

namespace Jira;

use App\Clients\JiraClient;
use App\Factories\IssueFactory;
use App\Models\IntegrationSetting;
use App\Models\User;
use App\Repositories\IssueRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IssueRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected IssueRepository $repository;

    public function setUp(): void
    {
        parent::setUp();

        $user = User::factory()->create();

        IntegrationSetting::factory()->create([
            'user_id' => $user->id,
            'jira_token' => 'fake-jira-token',
            'jira_email' => 'test@example.com',
            'jira_domain' => 'fake-jira.test',
            'github_token' => 'fake-gh',
            'github_username' => 'irrelevant',
            'github_repo' => 'irrelevant',
        ]);

        $this->actingAs($user);

        $this->repository = new IssueRepository(
            new JiraClient(),
            new IssueFactory()
        );
    }

    public function test_it_fetches_issues_by_fix_version()
    {
        $mockClient = \Mockery::mock(JiraClient::class);
        $mockClient->shouldReceive('get')
            ->with('/search', \Mockery::on(function ($query) {
                return str_contains($query['jql'], 'fixVersion="v1.0.0"');
            }))
            ->once()
            ->andReturn([
                'issues' => [
                    [
                        'key' => 'ABC-123',
                        'fields' => [
                            'summary' => 'Login werkt niet',
                            'status' => ['name' => 'To Do'],
                            'issuetype' => ['name' => 'Bug'],
                            'issuelinks' => [],
                            'parent' => null,
                        ]
                    ]
                ]
            ]);

        $repository = new IssueRepository($mockClient, new \App\Factories\IssueFactory());

        $issues = $repository->getIssuesByReleaseName('ABC', 'v1.0.0');

        $this->assertCount(1, $issues);
        $this->assertEquals('ABC-123', $issues[0]->getKey());
        $this->assertEquals('Login werkt niet', $issues[0]->getSummary());
        $this->assertEquals('To Do', $issues[0]->getStatus());
    }

    public function test_it_handles_empty_or_missing_fields()
    {
        $mockClient = \Mockery::mock(\App\Clients\JiraClient::class);
        $mockClient->shouldReceive('get')
            ->with('/search', \Mockery::any())
            ->once()
            ->andReturn([
                'issues' => [
                    [
                        'key' => 'EMPTY-1',
                        'fields' => [
                            // intentionally leaving out: summary, status, issuetype, parent, issuelinks
                        ]
                    ]
                ]
            ]);

        $repo = new \App\Repositories\IssueRepository($mockClient, new \App\Factories\IssueFactory());

        $issues = $repo->getIssuesByReleaseName('DUMMY', 'FAKE');

        $this->assertCount(1, $issues);
        $this->assertEquals('EMPTY-1', $issues[0]->getKey());
        $this->assertEquals('', $issues[0]->getSummary());       // Default fallback
        $this->assertEquals('Onbekend', $issues[0]->getStatus()); // Default fallback
        $this->assertEquals('Onbekend', $issues[0]->getType());   // Default fallback
        $this->assertNull($issues[0]->getParentKey());            // Null fallback
    }


    protected function tearDown(): void
    {
        \Mockery::close();
        parent::tearDown();
    }
}
