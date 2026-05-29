<?php

use App\Http\Controllers\Admin\Web\AuthController;
use App\Http\Controllers\Admin\Web\CategoryController;
use App\Http\Controllers\Admin\Web\ChampionshipController;
use App\Http\Controllers\Admin\Web\CircuitController;
use App\Http\Controllers\Admin\Web\DashboardController;
use App\Http\Controllers\Admin\Web\InscriptionController;
use App\Http\Controllers\Admin\Web\LocationController;
use App\Http\Controllers\Admin\Web\PaymentController;
use App\Http\Controllers\Admin\Web\RaceController;
use App\Http\Controllers\Admin\Web\ResultController;
use App\Http\Controllers\Admin\Web\SubscriptionController;
use App\Http\Controllers\Admin\Web\SubscriptionPlanController;
use App\Http\Controllers\Admin\Web\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('login', [AuthController::class, 'showLogin'])->name('login');
        Route::post('login', [AuthController::class, 'login'])->name('login.submit');
    });

    Route::middleware(['auth', 'admin'])->group(function () {
        Route::post('logout', [AuthController::class, 'logout'])->name('logout');
        Route::get('go-to-app', [AuthController::class, 'goToApp'])->name('go-to-app');
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
        Route::post('locations/geocode', [LocationController::class, 'geocode'])->name('locations.geocode');

        Route::resource('users', UserController::class)->except(['show']);
        Route::patch('users/{user}/toggle-active', [UserController::class, 'toggleActive'])->name('users.toggle-active');

        Route::resource('categories', CategoryController::class)->except(['show']);

        Route::resource('plans', SubscriptionPlanController::class)->except(['show']);

        Route::resource('subscriptions', SubscriptionController::class);
        Route::resource('payments', PaymentController::class);

        Route::resource('championships', ChampionshipController::class);
        Route::patch('championships/{championship}/status', [ChampionshipController::class, 'updateStatus'])->name('championships.status');

        Route::resource('circuits', CircuitController::class)->except(['show']);
        Route::patch('circuits/{circuit}/status', [CircuitController::class, 'updateStatus'])->name('circuits.status');

        Route::get('championships/{championship}/races', [RaceController::class, 'index'])->name('championships.races.index');
        Route::get('championships/{championship}/races/create', [RaceController::class, 'create'])->name('championships.races.create');
        Route::post('championships/{championship}/races', [RaceController::class, 'store'])->name('championships.races.store');
        Route::get('championships/{championship}/races/{race}/edit', [RaceController::class, 'edit'])->name('championships.races.edit');
        Route::put('championships/{championship}/races/{race}', [RaceController::class, 'update'])->name('championships.races.update');
        Route::delete('championships/{championship}/races/{race}', [RaceController::class, 'destroy'])->name('championships.races.destroy');

        Route::get('championships/{championship}/inscriptions', [InscriptionController::class, 'index'])->name('championships.inscriptions.index');
        Route::get('championships/{championship}/inscriptions/{inscription}/edit', [InscriptionController::class, 'edit'])->name('championships.inscriptions.edit');
        Route::put('championships/{championship}/inscriptions/{inscription}', [InscriptionController::class, 'update'])->name('championships.inscriptions.update');
        Route::patch('championships/{championship}/inscriptions/{inscription}/status', [InscriptionController::class, 'updateStatus'])->name('championships.inscriptions.status');
        Route::post('championships/{championship}/inscriptions/approve-pending', [InscriptionController::class, 'approveAllPending'])->name('championships.inscriptions.approve-pending');
        Route::delete('championships/{championship}/inscriptions/{inscription}', [InscriptionController::class, 'destroy'])->name('championships.inscriptions.destroy');

        Route::get('races/{race}/results', [ResultController::class, 'index'])->name('races.results.index');
        Route::get('races/{race}/results/create', [ResultController::class, 'create'])->name('races.results.create');
        Route::post('races/{race}/results', [ResultController::class, 'store'])->name('races.results.store');
        Route::get('races/{race}/results/{result}/edit', [ResultController::class, 'edit'])->name('races.results.edit');
        Route::put('races/{race}/results/{result}', [ResultController::class, 'update'])->name('races.results.update');
        Route::delete('races/{race}/results/{result}', [ResultController::class, 'destroy'])->name('races.results.destroy');
    });
});
