<?php

namespace App\Providers;

use App\Models\Championship;
use App\Models\Circuit;
use App\Models\Inscription;
use App\Models\Race;
use App\Models\Result;
use App\Policies\ChampionshipPolicy;
use App\Policies\CircuitPolicy;
use App\Policies\InscriptionPolicy;
use App\Policies\RacePolicy;
use App\Policies\ResultPolicy;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Paginator::useBootstrapFive();
        Paginator::defaultView('admin.partials.pagination');

        JsonResource::withoutWrapping();

        Gate::policy(Championship::class, ChampionshipPolicy::class);
        Gate::policy(Circuit::class, CircuitPolicy::class);
        Gate::policy(Inscription::class, InscriptionPolicy::class);
        Gate::policy(Race::class, RacePolicy::class);
        Gate::policy(Result::class, ResultPolicy::class);
    }
}
