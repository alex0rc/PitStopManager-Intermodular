<?php

namespace Database\Seeders\Concerns;

/**
 * Permanent go-kart themed image URLs (Unsplash License).
 * googleusercontent.com image_retrieval links are session-only and cannot be seeded.
 */
trait KartingImageUrls
{
    protected function kartingCircuitImageUrls(): array
    {
        $q = '?q=80&w=900&auto=format&fit=crop';

        return [
            'karting-932-electric'        => 'https://images.unsplash.com/photo-1753480864968-478960858ec9'.$q, // indoor electric, neon night
            'karting-alacant'             => 'https://images.unsplash.com/photo-1505570554449-69ce7d4fa36b'.$q, // outdoor grid, two drivers
            'karting-nucia-outdoor'       => 'https://images.unsplash.com/photo-1578401449027-acdcfd8137e9'.$q, // outdoor asphalt track
            'karting-orihuela-costa'      => 'https://images.unsplash.com/photo-1559968381-128fdcf0afd7'.$q, // go-kart close-up
            'racing-center-gilesias'      => 'https://images.unsplash.com/photo-1585099750803-b388c59bdb5c'.$q, // driver cockpit perspective
            'circuito-fernando-alonso'    => 'https://images.unsplash.com/photo-1640084347692-e8f6b84caa7c'.$q, // professional outdoor kart circuit
            'karting-campillos-malaga'    => 'https://images.unsplash.com/photo-1773909722972-1f5e533798b8'.$q, // multiple karts in corner
            'fast-kart-condomina'         => 'https://images.unsplash.com/photo-1682334863932-c746daff7cd9'.$q, // indoor facility
            'gokarts-mar-menor'           => 'https://images.unsplash.com/photo-1594507905944-a2c8c4050850'.$q, // sunny outdoor track
            'karting-ceuti-murcia'        => 'https://images.unsplash.com/photo-1585099750881-24be4ba1a998'.$q, // kart at speed on track
            'karting-los-garres'          => 'https://images.unsplash.com/photo-1503323553823-197edadf86b2'.$q, // go-kart chassis detail
            'circuit-ricardo-tormo'       => 'https://images.unsplash.com/photo-1774088047567-9acbd7d2c491'.$q, // large circuit, spectators
            'dakart-indoor-burjassot'     => 'https://images.unsplash.com/photo-1673498437299-da9488fa3752'.$q, // indoor track corner
            'karting-horta-nord'          => 'https://images.unsplash.com/photo-1642767049805-ea5be13ae9cc'.$q, // starting grid, multiple karts
            'karting-m4-paterna'          => 'https://images.unsplash.com/photo-1612450388361-7d78e64381f7'.$q, // indoor/outdoor rental kart
            'karting-nabella-saler'       => 'https://images.unsplash.com/photo-1595577586402-56e4612b6fa7'.$q, // wide outdoor track
            'kartodromo-lucas-guerrero'   => 'https://images.unsplash.com/photo-1560990817-4dea4e44b52e'.$q, // two karts racing
            'circuito-zuera-zaragoza'     => 'https://images.unsplash.com/photo-1560990816-d71bc29f1e29'.$q, // high-speed outdoor kart
        ];
    }

    protected function kartingChampionshipImageUrls(): array
    {
        $q = '?q=80&w=900&auto=format&fit=crop';

        return [
            'levante-kart-2026'       => 'https://images.unsplash.com/photo-1774088047567-9acbd7d2c491'.$q,
            'costa-blanca-kart'       => 'https://images.unsplash.com/photo-1752070127418-c8d73f6a8acd'.$q, // checkered flag finish
            'trofeo-valencia-kart'    => 'https://images.unsplash.com/photo-1560990816-d71bc29f1e29'.$q, // winner kart celebration
            'murcia-amateur-kart'     => 'https://images.unsplash.com/photo-1642767049805-ea5be13ae9cc'.$q, // amateur rental grid
            'cadetes-levante-iame'    => 'https://images.unsplash.com/photo-1537735107257-b0fec32b0ee2'.$q, // youth cadet kart
            'junior-challenge-bcosta' => 'https://images.unsplash.com/photo-1588683250983-59c41b61bcf4'.$q, // junior riders
            'iberian-kart-tour-ok'    => 'https://images.unsplash.com/photo-1773909722972-1f5e533798b8'.$q, // multi-kart tournament action
        ];
    }
}
