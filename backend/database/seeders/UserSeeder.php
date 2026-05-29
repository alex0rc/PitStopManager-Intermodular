<?php

namespace Database\Seeders;

use App\Models\PilotProfile;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $base = [
            [
                'name'              => 'Admin',
                'email'             => 'admin@pitstop.com',
                'role'              => 'admin',
                'email_verified_at' => now(),
            ],
            [
                'name'              => 'Carlos Organizer',
                'email'             => 'carlos@pitstop.com',
                'role'              => 'organizer',
                'email_verified_at' => now(),
            ],
            [
                'name'              => 'Maria Organizer',
                'email'             => 'maria@pitstop.com',
                'role'              => 'organizer',
                'email_verified_at' => now(),
            ],
            [
                'name'              => 'Pedro Costa Blanca',
                'email'             => 'pedro@pitstop.com',
                'role'              => 'organizer',
                'email_verified_at' => now(),
            ],
            [
                'name'              => 'Javier Murcia Kart',
                'email'             => 'javier.org@pitstop.com',
                'role'              => 'organizer',
                'email_verified_at' => now(),
            ],
        ];

        foreach ($base as $row) {
            User::updateOrCreate(
                ['email' => $row['email']],
                $row + ['password' => Hash::make('password'), 'is_active' => true]
            );
        }

        $pilots = [
            [
                'name'           => 'Alejandro García',
                'email'          => 'piloto1@pitstop.com',
                'nickname'       => 'AlexSpeed',
                'birth_date'     => '1998-03-15',
                'license_number' => 'CV-2026-0001',
                'bio'            => 'Piloto de Valencia. Especialista en circuitos técnicos del Levante.',
            ],
            [
                'name'           => 'Javier López',
                'email'          => 'piloto2@pitstop.com',
                'nickname'       => 'JaviRacing',
                'birth_date'     => '1995-07-22',
                'license_number' => 'CV-2026-0002',
                'bio'            => 'De Alicante. Campeón regional Costa Blanca 2025.',
            ],
            [
                'name'           => 'Pablo Martínez',
                'email'          => 'piloto3@pitstop.com',
                'nickname'       => 'PabloTurbo',
                'birth_date'     => '2000-11-08',
                'license_number' => 'CV-2026-0003',
                'bio'            => 'Piloto de Murcia. Rápido en trazados con muchas curvas.',
            ],
            [
                'name'           => 'Daniel Fernández',
                'email'          => 'piloto4@pitstop.com',
                'nickname'       => 'DaniFast',
                'birth_date'     => '1997-01-30',
                'license_number' => 'CV-2026-0004',
                'bio'            => 'Mecánico y piloto de Elche. Conoce cada kart de alquiler.',
            ],
            [
                'name'           => 'Sergio Romero',
                'email'          => 'piloto5@pitstop.com',
                'nickname'       => 'SergioKart',
                'birth_date'     => '1999-06-14',
                'license_number' => 'CV-2026-0005',
                'bio'            => 'De Gandía. Regular en Lucas Guerrero y Horta Nord.',
            ],
            [
                'name'           => 'Lucía Serrano',
                'email'          => 'piloto6@pitstop.com',
                'nickname'       => 'LuciaKart',
                'birth_date'     => '2002-04-12',
                'license_number' => 'CV-2026-0006',
                'bio'            => 'Pilota de Benidorm. Podios en Copa Costa Blanca.',
            ],
            [
                'name'           => 'Marcos Ortiz',
                'email'          => 'piloto7@pitstop.com',
                'nickname'       => 'MarcosO',
                'birth_date'     => '1994-09-03',
                'license_number' => 'CV-2026-0007',
                'bio'            => 'Veterano de karting en San Javier y Mar Menor.',
            ],
            [
                'name'           => 'Elena Torres',
                'email'          => 'piloto8@pitstop.com',
                'nickname'       => 'ElenaT',
                'birth_date'     => '2001-12-20',
                'license_number' => 'CV-2026-0008',
                'bio'            => 'De Torrent. Entrena en Dakart y circuitos indoor.',
            ],
            [
                'name'           => 'Hugo Navarro',
                'email'          => 'piloto9@pitstop.com',
                'nickname'       => 'HugoN',
                'birth_date'     => '2003-08-07',
                'license_number' => 'CV-2026-0009',
                'bio'            => 'Cadete de Cheste. Progresión rápida en categoría junior.',
            ],
            [
                'name'           => 'Irene Molina',
                'email'          => 'piloto10@pitstop.com',
                'nickname'       => 'IreneM',
                'birth_date'     => '1996-02-18',
                'license_number' => 'CV-2026-0010',
                'bio'            => 'Organiza quedadas en Gilesias y Karting Alacant los fines de semana.',
            ],
        ];

        foreach ($pilots as $data) {
            $user = User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'name'              => $data['name'],
                    'password'          => Hash::make('password'),
                    'role'              => 'pilot',
                    'is_active'         => true,
                    'email_verified_at' => now(),
                ]
            );

            PilotProfile::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'nickname'       => $data['nickname'],
                    'birth_date'     => $data['birth_date'],
                    'license_number' => $data['license_number'],
                    'bio'            => $data['bio'],
                ]
            );
        }
    }
}
