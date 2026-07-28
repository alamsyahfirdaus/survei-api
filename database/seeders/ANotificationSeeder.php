<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ANotificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = now();

        // Ambil seluruh user dengan role = user
        $users = DB::table('users')
            ->where('role', 'user')
            ->get();

        $titles = [
            'Selamat Datang',
            'Survey Berhasil Disimpan',
            'Survey Menunggu Review',
            'Survey Selesai',
            'Lokasi Berhasil Tersimpan',
            'Survey Baru',
            'Data Berhasil Diperbarui',
            'Terima Kasih',
            'Informasi Survey',
            'Pengingat Survey'
        ];

        $messages = [
            'Selamat datang di Aplikasi Field Survey.',
            'Data survey berhasil disimpan.',
            'Survey Anda sedang menunggu proses review.',
            'Survey telah berhasil diselesaikan.',
            'Koordinat GPS berhasil disimpan.',
            'Silakan lengkapi data survey Anda.',
            'Data survey berhasil diperbarui.',
            'Terima kasih telah berpartisipasi dalam survey.',
            'Terdapat informasi terbaru mengenai survey.',
            'Jangan lupa menyelesaikan survey yang masih berlangsung.'
        ];

        $notifications = [];

        foreach ($users as $user) {

            $notifications[] = [
                'user_id'    => $user->id,
                'title'      => $titles[array_rand($titles)],
                'message'    => $messages[array_rand($messages)],
                'is_read'    => rand(0, 1),
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        DB::table('a_notifications')->insert($notifications);
    }
}