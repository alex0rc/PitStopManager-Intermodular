<?php

namespace App\Support;

class AdminLabels
{
    public static function raceStatus(string $status): string
    {
        return match ($status) {
            'scheduled' => 'Programada',
            'in_progress' => 'En curso',
            'completed' => 'Completada',
            'cancelled' => 'Cancelada',
            default => $status,
        };
    }

    public static function inscriptionStatus(string $status): string
    {
        return match ($status) {
            'pending' => 'Pendiente',
            'confirmed' => 'Confirmada',
            'rejected' => 'Rechazada',
            'withdrawn' => 'Retirada',
            default => $status,
        };
    }

    public static function kartModality(string $modality): string
    {
        return match ($modality) {
            'rental' => 'Karts de alquiler',
            'own' => 'Kart propio',
            default => $modality,
        };
    }

    public static function championshipStatus(string $status): string
    {
        return match ($status) {
            'draft' => 'Borrador',
            'published' => 'Publicado',
            'in_progress' => 'En curso',
            'finished' => 'Finalizado',
            'cancelled' => 'Cancelado',
            default => $status,
        };
    }
}
