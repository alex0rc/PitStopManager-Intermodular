<?php

namespace Database\Seeders;

use App\Models\Circuit;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class CircuitSeeder extends Seeder
{
    public function run(): void
    {
        $carlos = User::where('email', 'carlos@pitstop.com')->firstOrFail();
        $maria  = User::where('email', 'maria@pitstop.com')->firstOrFail();
        $pedro  = User::where('email', 'pedro@pitstop.com')->firstOrFail();
        $javier = User::where('email', 'javier.org@pitstop.com')->firstOrFail();

        $circuits = [
            // Alicante
            [
                'user_id'       => $pedro->id,
                'name'          => 'Karting Alacant',
                'location'      => 'CV-821, km 3.8, Villafranqueza-Palamo',
                'city'          => 'Alicante',
                'province'      => 'Alicante',
                'country'       => 'España',
                'latitude'      => 38.3662,
                'longitude'     => -0.4761,
                'length_meters' => 750,
                'description'   => 'Referente del karting en Alicante desde 1988. Trazado técnico y flota de más de 40 karts.',
                'status'        => 'approved',
                'image_seed'    => 'karting-alacant',
            ],
            [
                'user_id'       => $pedro->id,
                'name'          => 'Karting 932 Electric Indoor',
                'location'      => 'C. Estaño, 2, San Vicente del Raspeig',
                'city'          => 'San Vicente del Raspeig',
                'province'      => 'Alicante',
                'country'       => 'España',
                'latitude'      => 38.3964,
                'longitude'     => -0.5187,
                'length_meters' => 504,
                'description'   => 'Primer circuito indoor eléctrico de la Comunitat Valenciana. Ideal para verano y noches.',
                'status'        => 'approved',
                'image_seed'    => 'karting-932-electric',
            ],
            [
                'user_id'       => $pedro->id,
                'name'          => 'Racing Center Gilesias',
                'location'      => 'N-332, km 74, San Fulgencio',
                'city'          => 'San Fulgencio',
                'province'      => 'Alicante',
                'country'       => 'España',
                'latitude'      => 38.1183,
                'longitude'     => -0.7124,
                'length_meters' => 420,
                'description'   => 'Pista de asfalto de primera calidad entre Alicante y Murcia. Karts para todas las edades.',
                'status'        => 'approved',
                'image_seed'    => 'racing-center-gilesias',
            ],
            [
                'user_id'       => $pedro->id,
                'name'          => 'Karting La Nucía Outdoor',
                'location'      => 'Polígono industrial, La Nucía',
                'city'          => 'Benidorm',
                'province'      => 'Alicante',
                'country'       => 'España',
                'latitude'      => 38.5361,
                'longitude'     => -0.1652,
                'length_meters' => 680,
                'description'   => 'Circuito exterior en la Costa Blanca, muy popular con turismo y equipos locales.',
                'status'        => 'approved',
                'image_seed'    => 'karting-nucia-outdoor',
            ],
            [
                'user_id'       => $pedro->id,
                'name'          => 'Karting Orihuela Costa',
                'location'      => 'Orihuela Costa, Vega Baja',
                'city'          => 'Orihuela',
                'province'      => 'Alicante',
                'country'       => 'España',
                'latitude'      => 37.9312,
                'longitude'     => -0.7345,
                'length_meters' => 550,
                'description'   => 'Instalaciones orientadas a aficionados y eventos de empresa en el sur de Alicante.',
                'status'        => 'approved',
                'image_seed'    => 'karting-orihuela-costa',
            ],

            // Valencia
            [
                'user_id'       => $maria->id,
                'name'          => 'Kartódromo Internacional Lucas Guerrero',
                'location'      => 'Crta. de Madrid km 405, Chiva',
                'city'          => 'Chiva',
                'province'      => 'Valencia',
                'country'       => 'España',
                'latitude'      => 39.4710,
                'longitude'     => -0.7190,
                'length_meters' => 1428,
                'description'   => 'El kartódromo más grande de la Comunitat Valenciana. Trazado principal de 1.428 m.',
                'status'        => 'approved',
                'image_seed'    => 'kartodromo-lucas-guerrero',
            ],
            [
                'user_id'       => $maria->id,
                'name'          => 'Circuit Ricardo Tormo',
                'location'      => 'Circuito Ricardo Tormo, Cheste',
                'city'          => 'Cheste',
                'province'      => 'Valencia',
                'country'       => 'España',
                'latitude'      => 39.4858,
                'longitude'     => -0.6276,
                'length_meters' => 1670,
                'description'   => 'Complejo de Cheste. Sede de pruebas internacionales y karting de alto nivel.',
                'status'        => 'approved',
                'image_seed'    => 'circuit-ricardo-tormo',
            ],
            [
                'user_id'       => $maria->id,
                'name'          => 'Karting Horta Nord',
                'location'      => 'Albalat dels Sorells',
                'city'          => 'Albalat dels Sorells',
                'province'      => 'Valencia',
                'country'       => 'España',
                'latitude'      => 39.5521,
                'longitude'     => -0.3524,
                'length_meters' => 900,
                'description'   => 'Circuito histórico del norte de Valencia, muy usado en campeonatos regionales.',
                'status'        => 'approved',
                'image_seed'    => 'karting-horta-nord',
            ],
            [
                'user_id'       => $maria->id,
                'name'          => 'Karting Nabella',
                'location'      => 'Pinedo, junto a la playa del Saler',
                'city'          => 'Valencia',
                'province'      => 'Valencia',
                'country'       => 'España',
                'latitude'      => 39.3350,
                'longitude'     => -0.3280,
                'length_meters' => 620,
                'description'   => 'Clásico veraniego junto al mar. Ambiente relajado y cronometraje preciso.',
                'status'        => 'approved',
                'image_seed'    => 'karting-nabella-saler',
            ],
            [
                'user_id'       => $maria->id,
                'name'          => 'Dakart Indoor',
                'location'      => 'Burjassot',
                'city'          => 'Burjassot',
                'province'      => 'Valencia',
                'country'       => 'España',
                'latitude'      => 39.5095,
                'longitude'     => -0.4133,
                'length_meters' => 480,
                'description'   => 'Karting indoor con luces LED y cronometraje al milésimo. Perfecto todo el año.',
                'status'        => 'approved',
                'image_seed'    => 'dakart-indoor-burjassot',
            ],
            [
                'user_id'       => $maria->id,
                'name'          => 'Karting M4',
                'location'      => 'Polígono Fuente del Jarro, Paterna',
                'city'          => 'Paterna',
                'province'      => 'Valencia',
                'country'       => 'España',
                'latitude'      => 39.5120,
                'longitude'     => -0.4520,
                'length_meters' => 720,
                'description'   => 'Circuito exterior con muchas curvas. Muy demandado para cumpleaños y ligas amateur.',
                'status'        => 'approved',
                'image_seed'    => 'karting-m4-paterna',
            ],

            // Murcia
            [
                'user_id'       => $javier->id,
                'name'          => 'Go-Karts Mar Menor',
                'location'      => 'San Javier, Mar Menor',
                'city'          => 'San Javier',
                'province'      => 'Murcia',
                'country'       => 'España',
                'latitude'      => 37.8060,
                'longitude'     => -0.8330,
                'length_meters' => 1100,
                'description'   => 'Gran pista junto al Mar Menor. Referencia en la Región de Murcia.',
                'status'        => 'approved',
                'image_seed'    => 'gokarts-mar-menor',
            ],
            [
                'user_id'       => $javier->id,
                'name'          => 'Karting Los Garres',
                'location'      => 'Gea y Truyols, Murcia',
                'city'          => 'Murcia',
                'province'      => 'Murcia',
                'country'       => 'España',
                'latitude'      => 38.0430,
                'longitude'     => -1.1050,
                'length_meters' => 850,
                'description'   => 'Kartódromo tradicional murciano con ambiente de campeonato regional.',
                'status'        => 'approved',
                'image_seed'    => 'karting-los-garres',
            ],
            [
                'user_id'       => $javier->id,
                'name'          => 'Fast Kart Condomina',
                'location'      => 'Parque Comercial Condomina, Murcia',
                'city'          => 'Murcia',
                'province'      => 'Murcia',
                'country'       => 'España',
                'latitude'      => 38.0145,
                'longitude'     => -1.1520,
                'length_meters' => 600,
                'description'   => 'Instalación urbana muy accesible para iniciación y eventos rápidos.',
                'status'        => 'approved',
                'image_seed'    => 'fast-kart-condomina',
            ],
            [
                'user_id'       => $javier->id,
                'name'          => 'Karting Ceutí',
                'location'      => 'Ceutí',
                'city'          => 'Ceutí',
                'province'      => 'Murcia',
                'country'       => 'España',
                'latitude'      => 38.0780,
                'longitude'     => -1.0680,
                'length_meters' => 580,
                'description'   => 'Cronometraje profesional al milésimo. Trazado técnico en el Altiplano murciano.',
                'status'        => 'approved',
                'image_seed'    => 'karting-ceuti-murcia',
            ],

            // Resto España
            [
                'user_id'       => $carlos->id,
                'name'          => 'Karting Campillos',
                'location'      => 'Campillos, Málaga',
                'city'          => 'Campillos',
                'province'      => 'Málaga',
                'country'       => 'España',
                'latitude'      => 37.0421,
                'longitude'     => -4.8554,
                'length_meters' => 1200,
                'description'   => 'Circuito andaluz de referencia con trazado técnico.',
                'status'        => 'approved',
                'image_seed'    => 'karting-campillos-malaga',
            ],
            [
                'user_id'       => $carlos->id,
                'name'          => 'Circuito de Zuera',
                'location'      => 'Zuera, Zaragoza',
                'city'          => 'Zuera',
                'province'      => 'Zaragoza',
                'country'       => 'España',
                'latitude'      => 41.8714,
                'longitude'     => -0.7894,
                'length_meters' => 1500,
                'description'   => 'Sede de campeonatos europeos. Uno de los mejores de España.',
                'status'        => 'approved',
                'image_seed'    => 'circuito-zuera-zaragoza',
            ],
            [
                'user_id'       => $carlos->id,
                'name'          => 'Circuito Fernando Alonso',
                'location'      => 'Llanera, Asturias',
                'city'          => 'Llanera',
                'province'      => 'Asturias',
                'country'       => 'España',
                'latitude'      => 43.4513,
                'longitude'     => -5.8395,
                'length_meters' => 1700,
                'description'   => 'Complejo del bicampeón del mundo con instalaciones de primer nivel.',
                'status'        => 'approved',
                'image_seed'    => 'circuito-fernando-alonso',
            ],
            [
                'user_id'       => $pedro->id,
                'name'          => 'Karting Villena (propuesta)',
                'location'      => 'Polígono Las Atalayas, Villena',
                'city'          => 'Villena',
                'province'      => 'Alicante',
                'country'       => 'España',
                'latitude'      => 38.6360,
                'longitude'     => -0.8650,
                'length_meters' => 900,
                'description'   => 'Nueva propuesta de circuito en el interior de Alicante. Pendiente de revisión.',
                'status'        => 'pending',
                'image_seed'    => 'karting-villena-propuesta',
            ],
        ];

        foreach ($circuits as $row) {
            $seed = $row['image_seed'];
            unset($row['image_seed']);

            $circuit = Circuit::updateOrCreate(
                ['name' => $row['name']],
                $row
            );

            if (empty($circuit->image)) {
                $image = $this->downloadImage(
                    "https://picsum.photos/seed/{$seed}/900/500",
                    'circuits',
                    "{$seed}.jpg"
                );
                if ($image) {
                    $circuit->update(['image' => $image]);
                }
            }
        }
    }

    private function downloadImage(string $url, string $folder, string $filename): ?string
    {
        try {
            $response = Http::timeout(10)->get($url);
            if ($response->successful()) {
                $path = "{$folder}/{$filename}";
                Storage::disk('public')->put($path, $response->body());
                return $path;
            }
        } catch (\Throwable) {}
        return null;
    }
}
