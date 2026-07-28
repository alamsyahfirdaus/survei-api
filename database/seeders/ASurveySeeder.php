<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ASurveySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = DB::table('users')
            ->where('role', 'user')
            ->orderBy('id')
            ->get();

        $titles = [
            'Jalan Berlubang',
            'Retakan Jembatan',
            'Drainase Tersumbat',
            'Lampu Jalan Mati',
            'Trotoar Rusak',
            'Sampah Menumpuk',
            'Pohon Tumbang',
            'Marka Jalan Pudar',
            'Rambu Rusak',
            'Genangan Air',
            'Saluran Air Rusak',
            'Pagar Pengaman Rusak',
            'Fasilitas Umum Rusak',
            'Bangunan Terbengkalai',
            'Kemacetan Lalu Lintas',
            'Parkir Liar',
            'Kerusakan Aspal',
            'Jembatan Rusak',
            'Lingkungan Kumuh',
            'Penerangan Kurang'
        ];

        $descriptions = [
            'Ditemukan kondisi yang memerlukan tindak lanjut dari pihak terkait.',
            'Hasil survei menunjukkan perlunya perbaikan pada lokasi tersebut.',
            'Objek survei memerlukan penanganan agar tidak membahayakan masyarakat.',
            'Kondisi lapangan telah didokumentasikan untuk proses tindak lanjut.',
            'Ditemukan beberapa kerusakan yang perlu segera diperbaiki.'
        ];

        foreach ($users as $index => $user) {

            $i = $index + 1;

            DB::table('a_surveys')->updateOrInsert(

                [
                    'title' => $titles[$index % count($titles)],
                    'user_id' => $user->id,
                ],

                [
                    'user_id' => $user->id,
                    'category_id' => (($index % 20) + 1),
                    'title' => $titles[$index % count($titles)],
                    'description' => $descriptions[array_rand($descriptions)],
                    'photo' => "survey/photo{$i}.jpg",
                    'latitude' => -7.3500000 + ($i * 0.001),
                    'longitude' => 108.2200000 + ($i * 0.001),
                    'address' => "Lokasi Survey {$i}, Kota Tasikmalaya",
                    'qr_code' => 'QR' . str_pad($i, 4, '0', STR_PAD_LEFT),
                    'status' => rand(0, 1) ? 'draft' : 'selesai',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}