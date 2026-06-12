<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Championship;
use App\Models\User;
use Database\Seeders\Concerns\KartingImageUrls;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class ChampionshipSeeder extends Seeder
{
    use KartingImageUrls;

    public function run(): void
    {
        $imageUrls = $this->kartingChampionshipImageUrls();
        $carlos = User::where('email', 'carlos@pitstop.com')->firstOrFail();
        $maria  = User::where('email', 'maria@pitstop.com')->firstOrFail();
        $pedro  = User::where('email', 'pedro@pitstop.com')->firstOrFail();
        $javier = User::where('email', 'javier.org@pitstop.com')->firstOrFail();

        $senior = Category::where('name', 'Senior')->firstOrFail();
        $cadete = Category::where('name', 'Cadete')->firstOrFail();
        $junior = Category::where('name', 'Junior')->firstOrFail();
        $master = Category::where('name', 'Master')->firstOrFail();

        $year = (int) now()->format('Y');

        $championships = [
            [
                'user_id'         => $carlos->id,
                'category_id'     => $senior->id,
                'name'            => 'Liga Levante Karting '.$year,
                'description'     => 'Campeonato estrella del Levante: Alicante, Valencia y Murcia. Modalidad rental.',
                'season_year'     => $year,
                'status'          => 'in_progress',
                'kart_modality'   => 'rental',
                'engine_class'    => 'Rental 390cc',
                'start_date'      => now()->startOfYear()->toDateString(),
                'end_date'        => now()->endOfYear()->toDateString(),
                'venue_country'   => 'EspaÃ±a',
                'venue_province'  => 'Valencia',
                'venue_city'      => 'Valencia',
                'venue_latitude'  => 39.4699,
                'venue_longitude' => -0.3763,
                'image_slug'      => 'levante-kart-2026',
            ],
            [
                'user_id'         => $pedro->id,
                'category_id'     => $senior->id,
                'name'            => 'Copa Costa Blanca '.$year,
                'description'     => 'Rondas en los mejores kartÃ³dromos de Alicante y sur de Valencia.',
                'season_year'     => $year,
                'status'          => 'published',
                'kart_modality'   => 'rental',
                'engine_class'    => 'Rental 390cc',
                'start_date'      => now()->subMonths(2)->toDateString(),
                'end_date'        => now()->addMonths(4)->toDateString(),
                'venue_country'   => 'EspaÃ±a',
                'venue_province'  => 'Alicante',
                'venue_city'      => 'Alicante',
                'venue_latitude'  => 38.3452,
                'venue_longitude' => -0.4810,
                'image_slug'      => 'costa-blanca-kart',
            ],
            [
                'user_id'         => $maria->id,
                'category_id'     => $senior->id,
                'name'            => 'Trofeo Comunitat Valenciana '.$year,
                'description'     => 'Circuitos de Valencia capital, Chiva, Cheste y Horta Nord.',
                'season_year'     => $year,
                'status'          => 'published',
                'kart_modality'   => 'rental',
                'engine_class'    => null,
                'start_date'      => now()->subMonth()->toDateString(),
                'end_date'        => now()->addMonths(5)->toDateString(),
                'venue_country'   => 'EspaÃ±a',
                'venue_province'  => 'Valencia',
                'venue_city'      => 'Chiva',
                'venue_latitude'  => 39.4710,
                'venue_longitude' => -0.7190,
                'image_slug'      => 'trofeo-valencia-kart',
            ],
            [
                'user_id'         => $javier->id,
                'category_id'     => $master->id,
                'name'            => 'Campeonato Murcia Amateur '.$year,
                'description'     => 'Liga amateur en Mar Menor, Los Garres y CeutÃ­.',
                'season_year'     => $year,
                'status'          => 'published',
                'kart_modality'   => 'rental',
                'engine_class'    => 'Rental senior',
                'start_date'      => now()->subMonths(1)->toDateString(),
                'end_date'        => now()->addMonths(6)->toDateString(),
                'venue_country'   => 'EspaÃ±a',
                'venue_province'  => 'Murcia',
                'venue_city'      => 'Murcia',
                'venue_latitude'  => 37.9922,
                'venue_longitude' => -1.1307,
                'image_slug'      => 'murcia-amateur-kart',
            ],
            [
                'user_id'         => $maria->id,
                'category_id'     => $cadete->id,
                'name'            => 'Copa Cadetes Levante '.$year,
                'description'     => 'Kart propio IAME X30. Valencia y Alicante.',
                'season_year'     => $year,
                'status'          => 'published',
                'kart_modality'   => 'own',
                'engine_class'    => 'IAME X30',
                'start_date'      => now()->toDateString(),
                'end_date'        => now()->addMonths(5)->toDateString(),
                'venue_country'   => 'EspaÃ±a',
                'venue_province'  => 'Valencia',
                'venue_city'      => 'Cheste',
                'venue_latitude'  => 39.4858,
                'venue_longitude' => -0.6276,
                'image_slug'      => 'cadetes-levante-iame',
            ],
            [
                'user_id'         => $pedro->id,
                'category_id'     => $junior->id,
                'name'            => 'Junior Challenge Alicante '.$year,
                'description'     => 'Campeonato junior en Costa Blanca (borrador).',
                'season_year'     => $year,
                'status'          => 'draft',
                'kart_modality'   => 'rental',
                'engine_class'    => null,
                'start_date'      => now()->addMonth()->toDateString(),
                'end_date'        => now()->endOfYear()->toDateString(),
                'venue_country'   => 'EspaÃ±a',
                'venue_province'  => 'Alicante',
                'venue_city'      => 'Benidorm',
                'venue_latitude'  => 38.5361,
                'venue_longitude' => -0.1652,
                'image_slug'      => 'junior-challenge-bcosta',
            ],
            [
                'user_id'         => $carlos->id,
                'category_id'     => $senior->id,
                'name'            => 'Iberian Kart Tour '.$year,
                'description'     => 'Etapa nacional: Levante + Zuera + Asturias.',
                'season_year'     => $year,
                'status'          => 'published',
                'kart_modality'   => 'own',
                'engine_class'    => 'OK Senior',
                'start_date'      => now()->subMonths(3)->toDateString(),
                'end_date'        => now()->addMonths(3)->toDateString(),
                'venue_country'   => 'EspaÃ±a',
                'venue_province'  => 'Zaragoza',
                'venue_city'      => 'Zuera',
                'venue_latitude'  => 41.8714,
                'venue_longitude' => -0.7894,
                'image_slug'      => 'iberian-kart-tour-ok',
            ],
        ];

        foreach ($championships as $row) {
            $slug = $row['image_slug'];
            $imageUrl = $imageUrls[$slug] ?? null;
            unset($row['image_slug']);

            $champ = Championship::updateOrCreate(
                ['name' => $row['name'], 'season_year' => $row['season_year']],
                $row
            );

            $this->assignImage($champ, $slug, $imageUrl, 'championships');

            if ($row['name'] === 'Liga Levante Karting '.$year) {
                $champ->touch();
            }
        }
    }

    private function assignImage(Championship $championship, string $slug, ?string $imageUrl, string $folder): void
    {
        $url = $imageUrl ?? (empty($championship->image) ? "https://picsum.photos/seed/{$slug}/900/500" : null);
        if (!$url) {
            return;
        }

        $image = $this->downloadImage($url, $folder, "{$slug}.jpg");
        if ($image) {
            $championship->update(['image' => $image]);
        }
    }

    private function downloadImage(string $url, string $folder, string $filename): ?string
    {
        try {
            $response = Http::timeout(20)
                ->withHeaders(['User-Agent' => 'PitStopManager-Seeder/1.0'])
                ->get($url);
            if ($response->successful()) {
                $path = "{$folder}/{$filename}";
                Storage::disk('public')->put($path, $response->body());
                return $path;
            }
        } catch (\Throwable) {}
        return null;
    }
}
