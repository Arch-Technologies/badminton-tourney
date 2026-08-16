<?php

use App\Http\Controllers\Organizer\BracketController;
use App\Http\Controllers\Organizer\EventController as OrganizerEventController;
use App\Http\Controllers\Organizer\MatchController;
use App\Http\Controllers\Organizer\PlayerController;
use App\Http\Controllers\Organizer\RegistrationController as OrganizerRegistrationController;
use App\Http\Controllers\Organizer\TournamentController as OrganizerTournamentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Public\EventController as PublicEventController;
use App\Http\Controllers\Public\RegistrationController as PublicRegistrationController;
use App\Http\Controllers\Public\TournamentController as PublicTournamentController;
use Illuminate\Support\Facades\Route;

// Public site
Route::get('/', [PublicTournamentController::class, 'index'])->name('home');
Route::get('/tournaments', [PublicTournamentController::class, 'index'])->name('tournaments.index');
Route::get('/tournaments/{tournament:slug}', [PublicTournamentController::class, 'show'])->name('tournaments.show');
Route::get('/tournaments/{tournament:slug}/events/{event}', [PublicEventController::class, 'show'])->name('tournaments.events.show');
Route::get('/tournaments/{tournament:slug}/events/{event}/register', [PublicRegistrationController::class, 'create'])->name('tournaments.events.register.create');
Route::post('/tournaments/{tournament:slug}/events/{event}/register', [PublicRegistrationController::class, 'store'])->name('tournaments.events.register.store');

Route::get('/dashboard', function () {
    return redirect()->route('organizer.tournaments.index');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Organizer area
Route::middleware(['auth', 'verified'])->prefix('organizer')->name('organizer.')->group(function () {
    Route::resource('players', PlayerController::class)->except(['show']);

    Route::resource('tournaments', OrganizerTournamentController::class);

    Route::prefix('tournaments/{tournament}/events')->name('tournaments.events.')->group(function () {
        Route::get('/create', [OrganizerEventController::class, 'create'])->name('create');
        Route::post('/', [OrganizerEventController::class, 'store'])->name('store');
        Route::get('/{event}', [OrganizerEventController::class, 'show'])->name('show');
        Route::get('/{event}/edit', [OrganizerEventController::class, 'edit'])->name('edit');
        Route::put('/{event}', [OrganizerEventController::class, 'update'])->name('update');
        Route::delete('/{event}', [OrganizerEventController::class, 'destroy'])->name('destroy');

        Route::post('/{event}/registrations', [OrganizerRegistrationController::class, 'store'])->name('registrations.store');
        Route::put('/{event}/registrations/{registration}', [OrganizerRegistrationController::class, 'update'])->name('registrations.update');
        Route::delete('/{event}/registrations/{registration}', [OrganizerRegistrationController::class, 'destroy'])->name('registrations.destroy');

        Route::post('/{event}/bracket', [BracketController::class, 'store'])->name('bracket.store');

        Route::get('/{event}/matches/{match}', [MatchController::class, 'edit'])->name('matches.edit');
        Route::put('/{event}/matches/{match}', [MatchController::class, 'update'])->name('matches.update');
        Route::patch('/{event}/matches/{match}/schedule', [MatchController::class, 'updateSchedule'])->name('matches.schedule');
    });
});

require __DIR__.'/auth.php';
