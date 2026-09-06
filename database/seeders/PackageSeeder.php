<?php

namespace Database\Seeders;

use App\Models\Package;
use Illuminate\Database\Seeder;

class PackageSeeder extends Seeder
{
    public function run(): void
    {
        $packages = [
            // ==========================================
            // PAKET RUMAH
            // ==========================================
            [
                'name' => 'LITE',
                'slug' => 'lite',
                'category' => 'rumah',
                'description' => 'Paket dekorasi rumah ekonomis dengan tampilan elegant dan elegan.',
                'features' => [
                    'Dekor Pelaminan 6m x 3m',
                    '1 Set Kursi Pelaminan',
                    'Meja Akad Lesehan + Permadani',
                    'Dekor Pintu Masuk',
                    'Welcome Sign',
                    'Standing Mirror',
                    '1 Set Standing Lamp',
                    'Lighting (4 Spot)',
                    'Stand Foto 1',
                    'Tong Uang 1',
                    'Artificial Premium Flower',
                    'Survey Lokasi',
                    'Implementasi Design 2D',
                ],
                'sort_order' => 1,
            ],
            [
                'name' => 'PURE',
                'slug' => 'pure',
                'category' => 'rumah',
                'description' => 'Paket rumah dengan panggung dekorasi dan pilihan dekor akad luar/dalam.',
                'features' => [
                    'Panggung Dekorasi 8m x 3m',
                    'Dekor Pelaminan 8m x 3m',
                    '1 Set Kursi Pelaminan',
                    'Dekor Akad Luar (6 Kursi Tiffani + Meja Akad) ATAU Dekor Akad Dalam (Meja Akad Lesehan + Dekor 3m)',
                    'Dekor Pintu Masuk',
                    'Welcome Sign',
                    'Standing Mirror',
                    '1 Set Standing Lamp',
                    'Lighting (4 Spot)',
                    'Stand Foto 2',
                    'Tong Uang 2',
                    'Artificial Premium Flower',
                    'Survey Lokasi',
                    'Implementasi Design 2D',
                ],
                'sort_order' => 2,
            ],
            [
                'name' => 'GRACE',
                'slug' => 'grace',
                'category' => 'rumah',
                'description' => 'Upgrade dari PURE dengan sentuhan bunga segar untuk keindahan lebih.',
                'features' => [
                    'Semua fasilitas paket PURE',
                    'Artificial Premium Flower Mix Fresh Flower',
                    'Taman Full Fresh Flower',
                ],
                'sort_order' => 3,
            ],
            [
                'name' => 'RADIANCE',
                'slug' => 'radiance',
                'category' => 'rumah',
                'description' => 'Paket mewah dengan pergola dan karpet jalan panjang.',
                'features' => [
                    'Semua fasilitas paket GRACE',
                    'Pergola (Lorong) 3 Set',
                    'Standing Flower 6',
                    'Karpet Jalan Max 30m',
                ],
                'sort_order' => 4,
            ],
            [
                'name' => 'ELYSIAN',
                'slug' => 'elysian',
                'category' => 'rumah',
                'description' => 'Paket premium dengan gazebo akad dan flooring initial eksklusif.',
                'features' => [
                    'Semua fasilitas paket RADIANCE',
                    'Gazebo Akad 3m x 3m',
                    'Flooring Initial 3m x 3m',
                ],
                'sort_order' => 5,
            ],
            [
                'name' => 'LUMINARY',
                'slug' => 'luminary',
                'category' => 'rumah',
                'description' => 'Paket full fresh flower dengan beam lighting dan pergola lengkap.',
                'features' => [
                    'Panggung Dekorasi 8m x 3m',
                    'Dekor Pelaminan 8m x 3m',
                    '1 Set Kursi Pelaminan',
                    'Dekor Akad (Luar / Dalam)',
                    'Dekor Pintu Masuk',
                    'Welcome Sign',
                    'Standing Mirror',
                    '1 Set Standing Lamp',
                    'Lighting (6 Spot) + Beam Lighting (4 Spot)',
                    'Stand Foto 2',
                    'Tong Uang 2',
                    'Full Fresh Flower',
                    'Pergola (Lorong) 3 Set',
                    'Standing Flower 6',
                    'Karpet Jalan Max 30m',
                    'Survey Lokasi',
                    'Implementasi Design 2D',
                ],
                'sort_order' => 6,
            ],
            [
                'name' => 'PRESTIGE',
                'slug' => 'prestige',
                'category' => 'rumah',
                'description' => 'Paket tertinggi rumah dengan gazebo akad 4x4 dan flooring eksklusif.',
                'features' => [
                    'Semua fasilitas paket LUMINARY',
                    'Gazebo Akad 4m x 4m',
                    'Flooring Initial 3m x 3m',
                ],
                'sort_order' => 7,
            ],

            // ==========================================
            // PAKET LAYOS
            // ==========================================
            [
                'name' => 'LAYOS 2 SET',
                'slug' => 'layos-2-set',
                'category' => 'layos',
                'description' => 'Dekorasi layos 2 set dengan gazebo pintu masuk.',
                'features' => [
                    '2 Set Layos (per set 9m x 6m)',
                    '1 Set Layos Modif / Hias (Atas Pelaminan)',
                    '1 Set Layos Seret Biasa',
                    'Gazebo Pintu Masuk 3m x 3m',
                    'Tirai 10-15m',
                ],
                'sort_order' => 8,
            ],
            [
                'name' => 'LAYOS 3 SET',
                'slug' => 'layos-3-set',
                'category' => 'layos',
                'description' => 'Dekorasi layos 3 set dengan gazebo pintu masuk yang lebih luas.',
                'features' => [
                    '3 Set Layos (per set 9m x 6m)',
                    '1 Set Layos Modif / Hias (Atas Pelaminan)',
                    '2 Set Layos Seret Biasa',
                    'Gazebo Pintu Masuk 3m x 3m',
                    'Tirai 10-15m',
                ],
                'sort_order' => 9,
            ],

            // ==========================================
            // PAKET GEDUNG
            // ==========================================
            [
                'name' => 'SMALL HALL',
                'slug' => 'small-hall',
                'category' => 'gedung_kecil',
                'description' => 'Dekorasi gedung kecil lengkap dengan photobooth dan gazebo akad.',
                'features' => [
                    'Dekor Pelaminan 8-10m',
                    '1 Set Kursi Pelaminan',
                    '1 Set Kursi Akad + Meja',
                    'Dekor Pintu Masuk',
                    'Dekor Photobooth',
                    'Welcome Sign',
                    'Standing Mirror',
                    'Standing Lamp 1 Set',
                    'Lighting (6 Spot) + Beam Lighting (4 Spot)',
                    'Stand Foto 2',
                    'Tong Uang 2',
                    'Artificial Premium Flower Mix Fresh Flower',
                    'Taman Full Fresh Flower',
                    'Pergola (Lorong) 3 Set',
                    'Standing Flower 6',
                    'Karpet Jalan Max 30m',
                    'Gazebo Akad 3x3m',
                    'Flooring (Initial) 3x3m',
                    'Survey Lokasi',
                    'Implementasi Design 2D',
                ],
                'sort_order' => 10,
            ],
            [
                'name' => 'GREAT HALL',
                'slug' => 'great-hall',
                'category' => 'gedung_besar',
                'description' => 'Dekorasi gedung besar premium dengan pelaminan 12-15m dan lighting maksimal.',
                'features' => [
                    'Dekor Pelaminan 12-15m',
                    '1 Set Kursi Pelaminan',
                    '1 Set Kursi Akad + Meja',
                    'Dekor Pintu Masuk',
                    'Dekor Photobooth',
                    'Welcome Sign',
                    'Standing Mirror',
                    'Standing Lamp 2 Set',
                    'Lighting (10 Spot) + Beam Lighting (6 Spot)',
                    'Stand Foto 2',
                    'Tong Uang 2',
                    'Artificial Premium Flower Mix Fresh Flower',
                    'Taman Full Fresh Flower',
                    'Pergola (Lorong) 5 Set',
                    'Standing Flower 6',
                    'Karpet Jalan Max 50m',
                    'Gazebo Akad 4x4m',
                    'Flooring (Initial) 3x3m',
                    'Survey Lokasi',
                    'Implementasi Design 2D',
                ],
                'sort_order' => 11,
            ],

            // ==========================================
            // PAKET ENGAGEMENT
            // ==========================================
            [
                'name' => 'ENGAGEMENT LITE',
                'slug' => 'engagement-lite',
                'category' => 'engagement',
                'description' => 'Dekorasi engagement simpel dan elegan untuk lamaran.',
                'features' => [
                    'Dekorasi 3M',
                    'Artificial Premium Flower',
                    'Inisial Nama',
                    'Lighting (2 Spot)',
                    'Standing Lamp 1 Set',
                    'Kursi Tiffani 2',
                    'Permadani',
                ],
                'sort_order' => 12,
            ],
            [
                'name' => 'ENGAGEMENT PREMIUM',
                'slug' => 'engagement-premium',
                'category' => 'engagement',
                'description' => 'Dekorasi engagement mewah dengan welcome gate dan bunga segar.',
                'features' => [
                    'Dekorasi 3-5M',
                    'Welcome Gate',
                    'Artificial Premium Flower + Mix Fresh Flower',
                    'Inisial Nama',
                    'Lighting (4 Spot)',
                    'Standing Lamp 1 Set',
                    'Kursi Tiffani 2',
                    'Standing Mirror',
                    'Permadani',
                ],
                'sort_order' => 13,
            ],
        ];

        foreach ($packages as $package) {
            Package::create($package);
        }

        $this->command->info('✅ 13 packages seeded successfully!');
    }
}
