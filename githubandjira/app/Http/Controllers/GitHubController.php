<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use App\Mail\MergeProposalCreated;

class GitHubController extends Controller
{
    /*public function createReleaseBranch(Request $request)
    {
        Config::get('jira.test');
        $releaseName = $request->input('releaseName');
        $releaseName = str_replace(' ', '-', $releaseName);  // Naam aanpassen

        // GitHub API interactie hier

        $repo = env('GITHUB_REPO');
        $token = env('GITHUB_TOKEN');

        // Haal de laatste commit van de hoofdbranch
        $response = Http::withToken($token)
            ->get("https://api.github.com/repos/{$repo}/git/refs/heads/main");

        if ($response->failed()) {
            return response()->json(['error' => 'Kan de laatste commit niet ophalen'], 400);
        }

        $latestCommitSha = $response->json()['object']['sha'];

        // Maak een nieuwe branch aan
        $newBranchResponse = Http::withToken($token)
            ->post("https://api.github.com/repos/{$repo}/git/refs", [
                'ref' => "refs/heads/{$releaseName}",
                'sha' => $latestCommitSha,
            ]);

        if ($newBranchResponse->failed()) {
            return response()->json(['error' => 'Fout bij het aanmaken van de branch'], 400);
        }

        // Return een succesvolle response
        return response()->json(['message' => "Branch '{$releaseName}' succesvol aangemaakt!"], 200);
    }*/
}
