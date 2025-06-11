<?php

namespace App\Services;

use App\Manager\IntegrationManager;

class IntegrationUrlService
{
    public function getJiraBaseUrl(): string
    {
        return IntegrationManager::current()->jira_url ?? 'https://happyhorizon.atlassian.net';
    }

    public function getGithubBaseUrl(): string
    {
        return IntegrationManager::current()->github_url ?? 'https://github.com/';
    }
}
