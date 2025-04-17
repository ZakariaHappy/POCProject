<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use http\Env\Response;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Http;

class SocialAuthController extends Controller
{
    // GitHub login (blijft hetzelfde)
    public function redirectToGitHub()
    {
        return Socialite::driver('github')->redirect();
    }

    public function dashboard()
    {
        return view('dashboard', [
            'githubUser' => session('github_user'),
            'jiraUser' => session('jira_user'),
        ]);
    }

    public function handleGitHubCallback()
    {
        $user = Socialite::driver('github')->user();
        session(['github_user' => $user]);

        session(['github_token' => $user->token]);



        return redirect('/dashboard')->with('success', 'Ingelogd met GitHub!');
    }

    // Jira login via OAuth 2.0 (handmatig)
    public function redirectToJira()
    {
        $query = http_build_query([
            'audience' => 'api.atlassian.com',
            'client_id' => config('services.jira.client_id'),
            'scope' => 'offline_access read:me read:jira-user read:jira-work read:project:jira',
            'redirect_uri' => config('services.jira.redirect'),
            'response_type' => 'code',
            'prompt' => 'consent',
            'state' => csrf_token(), // <-- belangrijk
        ]);

        return redirect()->away('https://auth.atlassian.com/authorize?' . $query);
    }

    public function handleJiraCallback(Request $request)
    {
        $response = Http::asForm()->post('https://auth.atlassian.com/oauth/token', [
            'grant_type' => 'authorization_code',
            'client_id' => config('services.jira.client_id'),
            'client_secret' => config('services.jira.client_secret'),
            'code' => $request->code,
            'redirect_uri' => config('services.jira.redirect'),
        ]);

        $data = $response->json();

        if (!isset($data['access_token'])) {
            return redirect('/')->with('error', 'Kon geen toegangstoken ophalen van Jira.');
        }

        $accessToken = $data['access_token'];

        session(['jira_token' => $accessToken]);

        $userResponse = Http::withToken($accessToken)->get('https://api.atlassian.com/me');
        $jiraUser = $userResponse->json();
        session(['jira_user' => $jiraUser]);


        return redirect('/dashboard')->with('success', 'Ingelogd met Jira!');
    }

    public function getGitHubRepositories()
    {
        $githubUser = session('github_user');
        if (!$githubUser) {
            return response()->json(['error' => 'Geen GitHub gebruiker ingelogd'], 401);
        }

        $accessToken = session('github_token'); // Haal token uit sessie

        if (!$accessToken) {
            return response()->json(['error' => 'Geen GitHub token gevonden'], 401); // Token is niet gevonden
        }

        $response = Http::withToken($accessToken)->get('https://api.github.com/user/repos');

        if ($response->failed()) {
            return response()->json(['error' => 'Kan geen repositories ophalen'], 400);
        }

        return response()->json($response->json());
    }

    public function getJiraProjects()
    {
        // Haal het Jira API-token uit de sessie
        $accessToken = session('jira_token');

        if (!$accessToken) {
            return response()->json(['error' => 'Geen Jira token gevonden'], 401);
        }

        // Haal de projecten op
        $response = Http::withToken($accessToken)->get('https://flashpointbv.atlassian.net/rest/api/3/project');




        return response()->json($response->json()); // Geef de projecten terug
    }
}
