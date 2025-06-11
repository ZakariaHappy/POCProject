<?php

use App\Livewire\Settings\Appearance;
use App\Livewire\Settings\IntegrationSettings;
use App\Livewire\Settings\Password;
use App\Livewire\Settings\Profile;
use Illuminate\Support\Facades\Route;
use App\Livewire\ReleaseWorkflowComponent;


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




Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/release', function () {
    return view('release');
})->middleware(['auth'])->name('release');

Route::get('/integration', function () {
    return view('integration');
})->middleware(['auth'])->name('integration');




require __DIR__.'/auth.php';
