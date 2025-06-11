<?php

namespace Database\Factories;

use App\Models\IntegrationSetting;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\IntegrationSetting>
 */
class IntegrationSettingFactory extends Factory
{
    protected $model = IntegrationSetting::class;

    public function definition(): array
    {
        return [
                'github_token' => 'fake-token',
                'github_username' => 'testuser',
                'github_repo' => 'test-repo',
                'jira_token' => 'fake-jira-token',
                'jira_domain' => 'fake-jira-domain',
                'user_id' => \App\Models\User::factory(),
        ];
    }
}
