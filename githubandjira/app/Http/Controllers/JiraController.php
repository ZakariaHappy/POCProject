<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class JiraController extends Controller
{
    public function showProjectByKey($key)
    {
        $email = env('JIRA_EMAIL');
        $accessToken = env('JIRA_TOKEN');

        try {
            // Haal project op via Jira API
            $response = Http::timeout(60)
                ->withBasicAuth($email, $accessToken)
                ->get("https://happyhorizon.atlassian.net/rest/api/3/project/{$key}");

            if ($response->successful()) {
                $project = $response->json();
                $error = null;
            } else {
                $project = null;
                $error = 'Kan project ' . $key . ' niet ophalen. Fout: ' . $response->body();
            }
        } catch (\Exception $e) {
            $project = null;
            $error = 'Er is een fout opgetreden: ' . $e->getMessage();
        }

        return view('dashboard', compact('project', 'error'));
    }
}
