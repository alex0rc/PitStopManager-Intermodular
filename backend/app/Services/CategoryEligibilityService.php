<?php

namespace App\Services;

use App\Models\Category;
use App\Models\User;
use Carbon\Carbon;

class CategoryEligibilityService
{
    public function validateForCategory(User $user, Category $category): ?string
    {
        $hasAgeRules = $category->min_age !== null || $category->max_age !== null;

        if (!$hasAgeRules) {
            return null;
        }

        $birthDate = $user->pilotProfile?->birth_date;

        if (!$birthDate) {
            return 'Completa tu fecha de nacimiento en el perfil de piloto para inscribirte en esta categoría.';
        }

        $age = Carbon::parse($birthDate)->age;

        if ($category->min_age !== null && $age < $category->min_age) {
            return "Debes tener al menos {$category->min_age} años para esta categoría.";
        }

        if ($category->max_age !== null && $age > $category->max_age) {
            return "La edad máxima para esta categoría es {$category->max_age} años.";
        }

        return null;
    }
}
