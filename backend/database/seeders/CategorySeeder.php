<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name'          => 'Mini',
                'description'   => 'Categoría de iniciación para los más jóvenes.',
                'min_age'       => 8,
                'max_age'       => 12,
                'max_weight_kg' => 60,
            ],
            [
                'name'          => 'Cadete',
                'description'   => 'Categoría intermedia para jóvenes pilotos en formación.',
                'min_age'       => 12,
                'max_age'       => 15,
                'max_weight_kg' => 75,
            ],
            [
                'name'          => 'Junior',
                'description'   => 'Categoría para pilotos adolescentes con experiencia.',
                'min_age'       => 15,
                'max_age'       => 18,
                'max_weight_kg' => 85,
            ],
            [
                'name'          => 'Senior',
                'description'   => 'Categoría principal para pilotos adultos.',
                'min_age'       => 18,
                'max_age'       => null,
                'max_weight_kg' => null,
            ],
            [
                'name'          => 'Master',
                'description'   => 'Categoría para pilotos veteranos con más de 30 años.',
                'min_age'       => 30,
                'max_age'       => null,
                'max_weight_kg' => null,
            ],
        ];

        foreach ($categories as $row) {
            Category::updateOrCreate(['name' => $row['name']], $row);
        }
    }
}
