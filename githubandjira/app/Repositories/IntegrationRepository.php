<?php

namespace App\Repositories;

use App\Models\IntegrationSetting;
use Illuminate\Support\Facades\Auth;

class IntegrationRepository
{
    public function getSettingsForUser(): ?IntegrationSetting
    {
        return Auth::user()->integration;
    }

    public function saveSettings(array $data): IntegrationSetting
    {
        return Auth::user()->integration()->updateOrCreate([], $data);
    }
}
