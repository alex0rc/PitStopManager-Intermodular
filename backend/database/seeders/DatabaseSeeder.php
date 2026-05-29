<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            CategorySeeder::class,
            SubscriptionPlanSeeder::class,
            CircuitSeeder::class,
            ChampionshipSeeder::class,
            RaceSeeder::class,
            InscriptionSeeder::class,
            ResultSeeder::class,
            SubscriptionSeeder::class,
            RaceReminderSeeder::class,
        ]);
    }
}
