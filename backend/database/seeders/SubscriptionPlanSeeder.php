<?php

namespace Database\Seeders;

use App\Models\SubscriptionPlan;
use Illuminate\Database\Seeder;

class SubscriptionPlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'name'              => 'Básico',
                'slug'              => 'basico',
                'description'       => 'Plan básico para organizadores que inician. Incluye 1 campeonato.',
                'price'             => 29.99,
                'duration_days'     => 30,
                'max_championships' => 1,
                'is_active'         => true,
            ],
            [
                'name'              => 'Profesional',
                'slug'              => 'profesional',
                'description'       => 'Plan profesional con hasta 3 campeonatos simultáneos.',
                'price'             => 59.99,
                'duration_days'     => 90,
                'max_championships' => 3,
                'is_active'         => true,
            ],
            [
                'name'              => 'Premium',
                'slug'              => 'premium',
                'description'       => 'Plan premium anual con hasta 10 campeonatos y soporte prioritario.',
                'price'             => 99.99,
                'duration_days'     => 365,
                'max_championships' => 10,
                'is_active'         => true,
            ],
        ];

        foreach ($plans as $row) {
            SubscriptionPlan::updateOrCreate(['slug' => $row['slug']], $row);
        }
    }
}
