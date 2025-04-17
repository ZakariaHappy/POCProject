<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use App\Mail\MergeProposalCreated;

class ProjectSelection extends Component
{
    public $githubRepositories = [];
    public $jiraProjects = [];
    public $selectedGithubRepo = null;
    public $selectedJiraProject = null;
    public $project = null;
    public $issues = [];
    public $branches = [];
    public $error = null;
    public $projectKey = null;
    public $githubRepoName = null;
    public $releaseName = null;
    public $matchedBranches = [];

    protected $githubService;
    protected $jiraService;
    protected $mergeProposalService;

    public $pullRequestResults = [];

    public $jiraError = null;
    public $githubError = null;



    public function __construct()
    {
        $this->githubService = new GitHubService();
        $this->jiraService = null;
        $this->githubError = null;
        $this->jiraError = null;

        if (!$this->githubService->isConfigured()) {
            $this->githubError = 'GitHub integratie is niet correct geconfigureerd. Ga naar je instellingen.';
        }

        try {
            $this->jiraService = new JiraService();
            if (!$this->jiraService->isConfigured()) {
                $this->jiraError = 'Jira integratie is niet correct geconfigureerd. Ga naar je instellingen.';
            }
        } catch (\Exception $e) {
            $this->jiraError = 'Jira integratie is niet correct geconfigureerd. Ga naar je instellingen.';
        }

        $this->mergeProposalService = new MergeProposalService();
    }



    public function fetchGithubRepositories()
    {
        $this->branches = [];
        $this->selectedGithubRepo = null;
        $this->githubError = null;

        if (!$this->githubService->getToken()) {
            $this->githubError = 'GitHub integratiegegevens ontbreken of zijn niet correct.';
            return;
        }

        if ($this->githubRepoName) {
            try {
                $this->githubRepositories = $this->githubService->fetchRepositories($this->githubRepoName);

                if (empty($this->githubRepositories)) {
                    $this->githubError = 'Geen repositories gevonden met deze naam.';
                }
            } catch (\Exception $e) {
                // Iets ging écht fout met de integratie zelf (bijv. 401, invalid token, etc.)
                $this->githubError = 'GitHub integratiegegevens zijn incorrect. Controleer je instellingen.';

                // optioneel voor devs
                // session()->flash('error_details', $e->getMessage());
            }
        }
    }





    public function selectRepoAndFetchBranches($repoName)
    {
        $this->selectedGithubRepo = $repoName;
        $this->fetchGithubBranches();
    }


    public function fetchGithubBranches()
    {
        if ($this->selectedGithubRepo) {
            $this->branches = $this->githubService->fetchBranches($this->selectedGithubRepo);
            $this->matchBranchesToIssues();
        }
    }

    public function fetchJiraProject()
    {
        try {
            if (!$this->jiraService || !$this->jiraService->isConfigured()) {
                $this->jiraError = 'Jira integratiegegevens ontbreken of zijn niet correct.';
                return;
            }

            $this->project = $this->jiraService->fetchProject($this->projectKey);
            $this->issues = $this->jiraService->fetchIssues($this->projectKey);
        } catch (\Exception $e) {
            $this->jiraError = 'Het Jira project kon niet worden opgehaald. Controleer je integratiegegevens.';

            // Optioneel voor dev debugging:
            // session()->flash('error_details', $e->getMessage());
        }
    }


    public function fetchJiraReleaseIssues()
    {
        if ($this->projectKey && $this->releaseName) {
            // Deze bevat nu al de 'issuelinks' dankzij je aanpassing in fetchReleaseIssues()
            $this->issues = $this->jiraService->fetchReleaseIssues($this->projectKey, $this->releaseName);
        }
    }


    public function createMergeProposals()
    {
        if (empty($this->matchedBranches)) {
            session()->flash('error_mergen', 'Er zijn geen gematchete branches gevonden.');
            return;
        }

        $pullRequestResults = [];

        foreach ($this->matchedBranches as $match) {
            $result = $this->mergeProposalService->createMergeProposal($match['branch'], $this->releaseName);

            if ($result['success']) {
                $pullRequestResults[] = [
                    'url' => $result['url'],
                    'mergeable' => $result['mergeable'],
                    'branch' => $match['branch'],
                    'issue' => $match['issue']['key'],
                ];
            } else {
                session()->flash('error_mergen', 'Er is een fout opgetreden tijdens het maken van een pull request voor branch: ' . $match['branch']);
                return;
            }
        }

        // ✅ Alleen als ALLES goed ging
        $this->pullRequestResults = $pullRequestResults;
        session()->flash('match_message', 'Mergevoorstellen succesvol aangemaakt!');
        $this->reset(['matchedBranches']);
    }




    public function matchBranchesToIssues()
    {
        if (!$this->issues || !$this->branches) {
            $this->matchedBranches = [];
            return;
        }

        $matches = [];

        foreach ($this->issues as $issue) {
            $issueKey = strtoupper($issue['key']);

            // Voeg linked issue keys toe
            $linkedKeys = [];

            foreach ($issue['fields']['issuelinks'] ?? [] as $link) {
                if (isset($link['inwardIssue'])) {
                    $linkedKeys[] = strtoupper($link['inwardIssue']['key']);
                } elseif (isset($link['outwardIssue'])) {
                    $linkedKeys[] = strtoupper($link['outwardIssue']['key']);
                }
            }

            // Combineer main issue key met linked keys
            $allKeysToCheck = array_merge([$issueKey], $linkedKeys);

            foreach ($this->branches as $branch) {
                $branchName = strtoupper($branch['name']);

                foreach ($allKeysToCheck as $key) {
                    if (strpos($branchName, $key) !== false) {
                        $matches[] = [
                            'issue' => $issue,
                            'branch' => $branch['name'],
                            'matched_on' => $key // optioneel: welke key matchte
                        ];
                        break; // geen dubbele match
                    }
                }
            }
        }

        $this->matchedBranches = $matches;
    }


    public function createReleaseBranch()
    {
        try {
            $releaseName = str_replace(' ', '-', $this->releaseName);
            $repo = $this->githubService->getRepo();
            $token = $this->githubService->getToken();

            // Stap 1: Haal de SHA van de main branch op
            $shaResponse = Http::withToken($token)
                ->get("https://api.github.com/repos/{$repo}/git/refs/heads/main");

            if ($shaResponse->failed()) {
                session()->flash('error', 'Kan de laatste commit niet ophalen van main branch.');
                session()->flash('error_details', $shaResponse->body());
                return;
            }

            $latestCommitSha = $shaResponse->json()['object']['sha'];

            // Stap 2: Maak nieuwe branch aan
            $newBranchResponse = Http::withToken($token)
                ->post("https://api.github.com/repos/{$repo}/git/refs", [
                    'ref' => "refs/heads/{$releaseName}",
                    'sha' => $latestCommitSha,
                ]);

            if ($newBranchResponse->successful()) {
                session()->flash('jira_message', "Release branch '{$releaseName}' succesvol aangemaakt!");
            } else {
                session()->flash('error', 'Fout bij het aanmaken van de release branch!');
                session()->flash('error_details', $newBranchResponse->body());
            }
        } catch (\Exception $e) {
            session()->flash('error', 'Er is een fout opgetreden!');
            session()->flash('error_details', $e->getMessage());
        }
    }







    public function render()
    {
        return view('livewire.project-selection', [
            'jiraError' => $this->jiraError,
            'githubError' => $this->githubError,
        ]);
    }
}
