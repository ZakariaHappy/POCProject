<?php

use App\Livewire\Settings\Appearance;
use App\Livewire\Settings\Password;
use App\Livewire\Settings\Profile;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\SocialAuthController;
use App\Http\Controllers\JiraController;
use App\Http\Controllers\GitHubController;
use App\Livewire\ProjectSelection;
use App\Livewire\Settings\UserIntegrationSettings;


Route::get('/', function () {
    return view('welcome');
})->name('home');



Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::redirect('settings', 'settings/profile');

    Route::get('settings/profile', Profile::class)->name('settings.profile');
    Route::get('settings/password', Password::class)->name('settings.password');
    Route::get('settings/appearance', Appearance::class)->name('settings.appearance');
});

Route::get('/login/github', [SocialAuthController::class, 'redirectToGitHub']);
Route::get('/login/github/callback', [SocialAuthController::class, 'handleGitHubCallback']);

Route::get('/login/jira', [SocialAuthController::class, 'redirectToJira']);
Route::get('/login/jira/callback', [SocialAuthController::class, 'handleJiraCallback']);

Route::get('/jira/test', function () {
    $token = session('jira_access_token');

    $response = Http::withToken($token)->get('https://api.atlassian.com/oauth/token/accessible-resources');

    return $response->json();
});




Route::get('/project/{key}', [JiraController::class, 'showProjectByKey'])->name('project.show');


Route::get('/dashboard', function () {
    return view('dashboard');
})->name('dashboard');


//Route::post('/create-release-branch', [GitHubController::class, 'createReleaseBranch'])->name('create.release.branch');


Route::post('/create-merge-proposals', [GitHubController::class, 'createMergeProposals'])->name('create.merge.proposals');

Route::middleware(['auth'])->get('/settings/integrations', function () {
    return view('integrations'); // blade file heet integrations.blade.php
})->name('settings.integrations');

Route::middleware(['auth'])->get('/project-selection', ProjectSelection::class)->name('project.selection');




require __DIR__.'/auth.php';
