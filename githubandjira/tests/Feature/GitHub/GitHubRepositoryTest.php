<?php

namespace GitHub;

use App\Clients\GitHubClient;
use App\Factories\BranchFactory;
use App\Factories\GitHubFactory;
use App\Models\IntegrationSetting;
use App\Models\Issue;
use App\Models\Repository;
use App\Models\User;
use App\Repositories\GitHubRepository;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GitHubRepositoryTest extends TestCase
{
    use DatabaseMigrations;
    protected GitHubRepository $gitHubRepository;

    public function setUp(): void
    {
        parent::setUp();

        // Maak test user + integratie
        $user = User::factory()->create();

        IntegrationSetting::factory()->create([
            'user_id' => $user->id,
            'github_token' => 'fake-token',
            'github_username' => 'testuser',
            'github_repo' => 'test-repo',
            'jira_token' => 'fake-jira',
            'jira_domain' => 'fake-jira.test'
        ]);

        $this->actingAs($user);

        // 🔁 Zet alleen de algemene GitHub repository fake hier
        Http::fake([
            'https://api.github.com/repos/testuser/test-repo' => Http::response([
                'name' => 'test-repo',
                'full_name' => 'testuser/test-repo',
                'private' => true,
            ], 200),
        ]);

        $client = new GitHubClient();
        $factory = new GitHubFactory();
        $branchFactory = new BranchFactory();
        $pullRequestFactory = new \App\Factories\PullRequestFactory(); // ← toegevoegd

        $this->gitHubRepository = new GitHubRepository(
            $client,
            $factory,
            $branchFactory,
            $pullRequestFactory // ← toegevoegd!
        );
    }



    public function test_it_returns_repository_model_from_api()
    {
        $repository = $this->gitHubRepository->getRepository('test-repo');

        $this->assertInstanceOf(Repository::class, $repository);
        $this->assertEquals('test-repo', $repository->getName());
        $this->assertEquals('testuser/test-repo', $repository->getFullName());
        $this->assertTrue($repository->isPrivate());
    }

    public function test_it_fetches_and_filters_relevant_branches()
    {
        Http::fake([
            '*' => function ($request) {
                if (str_contains($request->url(), '/branches')) {
                    return Http::response([
                        [
                            'name' => 'feature/ABC-123-login',
                            'commit' => ['sha' => 'abc123']
                        ],
                        [
                            'name' => 'bugfix/ABC-456-dashboard',
                            'commit' => ['sha' => 'def456']
                        ],
                        [
                            'name' => 'hotfix/DEF-789-critical',
                            'commit' => ['sha' => 'ghi789']
                        ],
                    ], 200);
                }

                // fallback: repo details
                return Http::response([
                    'name' => 'test-repo',
                    'full_name' => 'testuser/test-repo',
                    'private' => true,
                ], 200);
            },
        ]);

        $jiraIssues = [
            new Issue('ABC-123', '', 'Onbekend', 'Onbekend'),
            new Issue('ABC-456', '', 'Onbekend', 'Onbekend'),
        ];

        $branches = $this->gitHubRepository->getRelevantBranches($jiraIssues, 'test-repo');

        $this->assertCount(2, $branches);
        $this->assertEquals('feature/ABC-123-login', $branches[0]->getName());
        $this->assertEquals('bugfix/ABC-456-dashboard', $branches[1]->getName());
    }


    public function test_it_creates_a_pull_request_from_branch_to_release_branch()
    {
        Http::fake([
            // 1. SHA ophalen van main
            'https://api.github.com/repos/testuser/test-repo/git/refs/heads/main' => Http::response([
                'object' => ['sha' => 'mainsha123']
            ], 200),

            // 2. Release branch aanmaken
            'https://api.github.com/repos/testuser/test-repo/git/refs' => Http::response([], 201),

            // 3. Pull request aanmaken
            'https://api.github.com/repos/testuser/test-repo/pulls' => Http::response([
                'number' => 42,
                'html_url' => 'https://github.com/testuser/test-repo/pull/42'
            ], 201),

            // 4. Pull request details ophalen
            'https://api.github.com/repos/testuser/test-repo/pulls/42' => Http::response([
                'mergeable' => true
            ], 200),
        ]);

        $pullRequest = $this->gitHubRepository->createMergeProposal(
            'test-repo',
            'feature/ABC-123-login',
            'Release 1.2.3'
        );

        $this->assertInstanceOf(\App\Models\PullRequest::class, $pullRequest);
        $this->assertEquals('feature/ABC-123-login', $pullRequest->branch);
        $this->assertEquals('feature/ABC-123-login', $pullRequest->issue);
        $this->assertEquals('https://github.com/testuser/test-repo/pull/42', $pullRequest->url);
        $this->assertTrue($pullRequest->mergeable);
    }

    public function test_it_generates_merge_proposals_overview()
    {
        Http::fake([
            // PR aanmaken
            'https://api.github.com/repos/testuser/test-repo/pulls' => Http::response([
                'number' => 42,
                'html_url' => 'https://github.com/testuser/test-repo/pull/42',
            ], 201),

            // PR details ophalen
            'https://api.github.com/repos/testuser/test-repo/pulls/42' => Http::response([
                'mergeable' => true,
            ], 200),
        ]);

        $pr = $this->gitHubRepository->createMergeProposal('test-repo', 'feature/ABC-123-login', 'release-1.0');

        $this->assertInstanceOf(\App\Models\PullRequest::class, $pr);
        $this->assertEquals('https://github.com/testuser/test-repo/pull/42', $pr->url);
        $this->assertEquals('feature/ABC-123-login', $pr->branch);
        $this->assertEquals(true, $pr->mergeable);
        $this->assertFalse($pr->hasConflict());
    }
}
