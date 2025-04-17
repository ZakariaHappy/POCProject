<?php

namespace App\Livewire\Settings;

use Livewire\Component;
use App\Models\IntegrationSetting;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;

class UserIntegrationSettings extends Component
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
        'github_repo'     => 'required|string',
        'jira_email'      => 'required|email',
        'jira_token'      => 'required|string',
        'jira_domain'     => 'required|string',
    ];

    public function mount()
    {
        $user = auth()->user();
        $integration = $user->integration;

        if ($integration) {
            $this->github_username = $integration->github_username;
            $this->github_token = $integration->github_token;
            $this->github_repo = $integration->github_repo;
            $this->jira_email = $integration->jira_email;
            $this->jira_token = $integration->jira_token;
            $this->jira_domain = $integration->jira_domain;
        }
    }

    public function save()
    {
        $this->validate();

        auth()->user()->integration()->updateOrCreate([], [
            'github_token'    => $this->github_token,
            'github_username' => $this->github_username,
            'github_repo'     => $this->github_repo,
            'jira_email'      => $this->jira_email,
            'jira_token'      => $this->jira_token,
            'jira_domain'     => $this->jira_domain,
        ]);

        session()->flash('message', 'Integratie-instellingen succesvol opgeslagen!');
        $this->dispatch('integration-saved');
    }

    public function render()
    {
        return view('livewire.settings.user-integration-settings');
    }
}
