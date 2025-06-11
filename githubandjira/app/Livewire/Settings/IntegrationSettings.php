<?php

namespace App\Livewire\Settings;

use App\Repositories\IntegrationRepository;
use Livewire\Component;

class IntegrationSettings extends Component
{
    public $github_token;
    public $github_username;
    public $github_repo;
    public $jira_email;
    public $jira_token;
    public $jira_domain;

    protected $rules = [
        'github_username' => 'required|string',
        'github_token'    => 'required|string',
//        'github_repo'     => 'required|string',
        'jira_email'      => 'required|email',
        'jira_token'      => 'required|string',
        'jira_domain'     => 'required|string',
    ];

    public function mount(IntegrationRepository $repository)
    {
        $data = $repository->getSettingsForUser();

        $this->github_username = $data['github_username'] ?? '';
        $this->github_token    = $data['github_token'] ?? '';
//        $this->github_repo     = $data['github_repo'] ?? '';
        $this->jira_email      = $data['jira_email'] ?? '';
        $this->jira_token      = $data['jira_token'] ?? '';
        $this->jira_domain     = $data['jira_domain'] ?? '';
    }

    public function save(IntegrationRepository $repository)
    {
        $this->validate();

        $repository->saveSettings([
            'github_token'    => $this->github_token,
            'github_username' => $this->github_username,
//            'github_repo'     => $this->github_repo,
            'jira_email'      => $this->jira_email,
            'jira_token'      => $this->jira_token,
            'jira_domain'     => $this->jira_domain,
        ]);

        session()->flash('message', 'Integratie-instellingen succesvol opgeslagen!');
        $this->dispatch('integration-saved');
    }

    public function getMaskedGithubTokenProperty()
    {
        return $this->github_token
            ? str_repeat('*', max(strlen($this->github_token) - 4, 0)) . substr($this->github_token, -4)
            : '';
    }

    public function getMaskedJiraTokenProperty()
    {
        return $this->jira_token
            ? str_repeat('*', max(strlen($this->jira_token) - 4, 0)) . substr($this->jira_token, -4)
            : '';
    }

    public function getMaskedJiraDomainProperty()
    {
        return $this->jira_domain
            ? str_repeat('*', max(strlen($this->jira_domain) - 4, 0)) . substr($this->jira_domain, -4)
            : '';
    }
}
