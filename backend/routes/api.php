<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\SubscriptionPlanController as AdminSubscriptionPlanController;
use App\Http\Controllers\Admin\SubscriptionController as AdminSubscriptionController;
use App\Http\Controllers\Admin\PaymentController as AdminPaymentController;
use App\Http\Controllers\ChampionshipController;
use App\Http\Controllers\CircuitController;
use App\Http\Controllers\RaceController;
use App\Http\Controllers\InscriptionController;
use App\Http\Controllers\ResultController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\WeatherController;
use App\Http\Controllers\Webhook\StripeWebhookController;
use Illuminate\Support\Facades\Route;

// Public auth routes — throttled to mitigate brute-force / scraping.
Route::middleware('throttle:10,1')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
});

// Public read-only routes
Route::get('/championships', [ChampionshipController::class, 'index']);
Route::get('/championships/featured', [ChampionshipController::class, 'featured']);
Route::get('/championships/{championship}', [ChampionshipController::class, 'show']);
Route::get('/championships/{championship}/standings', [ChampionshipController::class, 'standings']);
Route::get('/championships/{championship}/race-results', [ChampionshipController::class, 'raceResults']);
Route::get('/championships/{championship}/races', [RaceController::class, 'index']);
Route::get('/races/{race}', [RaceController::class, 'show']);
Route::get('/races/{race}/results', [ResultController::class, 'index']);
Route::get('/circuits', [CircuitController::class, 'index']);
Route::get('/circuits/provinces/list', [CircuitController::class, 'provinces']);
Route::get('/circuits/{circuit}', [CircuitController::class, 'show']);
Route::get('/categories', [AdminCategoryController::class, 'index']);
Route::get('/categories/{category}', [AdminCategoryController::class, 'show']);
Route::get('/subscription-plans', [AdminSubscriptionPlanController::class, 'index']);
Route::get('/weather', [WeatherController::class, 'index']);
Route::get('/locations/countries', [LocationController::class, 'countries']);
Route::get('/locations/provinces', [LocationController::class, 'provinces']);
Route::get('/locations/cities', [LocationController::class, 'cities']);

// Stripe webhook (no auth)
Route::post('/webhooks/stripe', [StripeWebhookController::class, 'handle']);

// Authenticated routes
Route::middleware(['auth:sanctum', 'active'])->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    // Profile
    Route::get('/profile', [ProfileController::class, 'show']);
    Route::put('/profile', [ProfileController::class, 'update']);
    Route::post('/profile/avatar', [ProfileController::class, 'uploadAvatar']);
    Route::get('/profile/pilot', [ProfileController::class, 'showPilotProfile']);
    Route::put('/profile/pilot', [ProfileController::class, 'updatePilotProfile']);

    Route::get('/my/championships', [ChampionshipController::class, 'myChampionships'])
        ->middleware('role:organizer,admin');
    Route::get('/my/circuits', [CircuitController::class, 'myCircuits'])
        ->middleware('role:organizer,admin');

    // Championships (write operations)
    Route::post('/championships', [ChampionshipController::class, 'store'])
        ->middleware('role:organizer,admin');
    Route::put('/championships/{championship}', [ChampionshipController::class, 'update'])
        ->middleware('role:organizer,admin');
    Route::delete('/championships/{championship}', [ChampionshipController::class, 'destroy'])
        ->middleware('role:organizer,admin');
    Route::patch('/championships/{championship}/status', [ChampionshipController::class, 'updateStatus'])
        ->middleware('role:organizer,admin');
    Route::post('/championships/{championship}/image', [ChampionshipController::class, 'uploadImage'])
        ->middleware('role:organizer,admin');
    Route::post('/locations/geocode', [LocationController::class, 'geocode']);

    // Circuits (write operations)
    Route::post('/circuits', [CircuitController::class, 'store'])
        ->middleware('role:organizer,admin');
    Route::put('/circuits/{circuit}', [CircuitController::class, 'update'])
        ->middleware('role:organizer,admin');
    Route::delete('/circuits/{circuit}', [CircuitController::class, 'destroy'])
        ->middleware('role:organizer,admin');
    Route::post('/circuits/{circuit}/image', [CircuitController::class, 'uploadImage'])
        ->middleware('role:organizer,admin');

    // Races (write operations)
    Route::post('/championships/{championship}/races', [RaceController::class, 'store'])
        ->middleware('role:organizer,admin');
    Route::put('/races/{race}', [RaceController::class, 'update'])
        ->middleware('role:organizer,admin');
    Route::delete('/races/{race}', [RaceController::class, 'destroy'])
        ->middleware('role:organizer,admin');

    // Inscriptions
    Route::get('/championships/{championship}/inscriptions', [InscriptionController::class, 'index']);
    Route::post('/championships/{championship}/inscriptions', [InscriptionController::class, 'store']);
    Route::patch('/inscriptions/{inscription}/status', [InscriptionController::class, 'updateStatus'])
        ->middleware('role:organizer,admin');
    Route::put('/inscriptions/{inscription}/races', [InscriptionController::class, 'updateRaces']);
    Route::delete('/inscriptions/{inscription}/races/{race}', [InscriptionController::class, 'detachRace'])
        ->middleware('role:organizer,admin');
    Route::delete('/inscriptions/{inscription}', [InscriptionController::class, 'destroy']);
    Route::get('/my/inscriptions', [InscriptionController::class, 'myInscriptions']);

    // Results (write operations)
    Route::post('/races/{race}/results', [ResultController::class, 'store'])
        ->middleware('role:organizer,admin');
    Route::put('/results/{result}', [ResultController::class, 'update'])
        ->middleware('role:organizer,admin');
    Route::delete('/results/{result}', [ResultController::class, 'destroy'])
        ->middleware('role:organizer,admin');
    Route::get('/my/results', [ResultController::class, 'myResults']);

    // Subscriptions — pilots can subscribe to upgrade to organizer; organizers can renew.
    Route::post('/subscriptions', [SubscriptionController::class, 'store'])
        ->middleware('role:pilot,organizer');
    Route::post('/subscriptions/confirm', [SubscriptionController::class, 'confirmCheckout'])
        ->middleware('role:pilot,organizer');
    Route::get('/my/subscription', [SubscriptionController::class, 'mySubscription']);
    Route::get('/my/payments', [SubscriptionController::class, 'myPayments']);
    Route::get('/my/payments/{payment}/pdf', [SubscriptionController::class, 'downloadPdf']);

    // Admin routes
    Route::middleware('role:admin')->prefix('admin')->group(function () {
        Route::get('/users', [AdminUserController::class, 'index']);
        Route::get('/users/{user}', [AdminUserController::class, 'show']);
        Route::put('/users/{user}', [AdminUserController::class, 'update']);
        Route::delete('/users/{user}', [AdminUserController::class, 'destroy']);
        Route::patch('/users/{user}/toggle-active', [AdminUserController::class, 'toggleActive']);

        Route::post('/categories', [AdminCategoryController::class, 'store']);
        Route::put('/categories/{category}', [AdminCategoryController::class, 'update']);
        Route::delete('/categories/{category}', [AdminCategoryController::class, 'destroy']);

        Route::post('/subscription-plans', [AdminSubscriptionPlanController::class, 'store']);
        Route::put('/subscription-plans/{subscriptionPlan}', [AdminSubscriptionPlanController::class, 'update']);
        Route::delete('/subscription-plans/{subscriptionPlan}', [AdminSubscriptionPlanController::class, 'destroy']);

        Route::get('/subscriptions', [AdminSubscriptionController::class, 'index']);
        Route::get('/payments', [AdminPaymentController::class, 'index']);
    });
});
