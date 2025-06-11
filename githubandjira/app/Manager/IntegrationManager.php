<?php

namespace App\Manager;

use App\Models\IntegrationSetting;

class IntegrationManager
{
    public static function current(): ?IntegrationSetting
    {
        return auth()->user()?->integration;
    }
}
