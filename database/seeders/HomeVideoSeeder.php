<?php

namespace Database\Seeders;

use App\Models\HomeVideo;
use Illuminate\Database\Seeder;

class HomeVideoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $videos = [
            [
                'title' => 'Majestic Oud Perfume Review',
                'description' => 'Merasakan aroma elegan dan kemewahan sejati dari Majestic Oud. Pilihan terbaik untuk menemani momen istimewa Anda. ✨ #JoudahStore #MajesticOud',
                'external_video_url' => 'https://assets.mixkit.co/videos/preview/mixkit-spinning-perfume-bottle-in-slow-motion-41857-large.mp4',
                'thumbnail_path' => 'https://images.unsplash.com/photo-1541643600914-78b084683601?auto=format&fit=crop&q=80&w=600',
                'social_media_url' => 'https://www.instagram.com/joudahstore',
                'social_media_platform' => 'instagram',
                'sort_order' => 1,
            ],
            [
                'title' => 'How to Apply Oud Perfume Correctly',
                'description' => 'Tips sederhana agar wangi parfum kesayangan Anda bertahan seharian! Tonton selengkapnya dan bagikan ke teman-temanmu. Spray and shine! 💫',
                'external_video_url' => 'https://assets.mixkit.co/videos/preview/mixkit-hand-holding-a-perfume-bottle-and-spraying-it-41858-large.mp4',
                'thumbnail_path' => 'https://images.unsplash.com/photo-1594035910387-fea47794261f?auto=format&fit=crop&q=80&w=600',
                'social_media_url' => 'https://www.tiktok.com/@joudahstore',
                'social_media_platform' => 'tiktok',
                'sort_order' => 2,
            ],
        ];

        foreach ($videos as $video) {
            HomeVideo::updateOrCreate(
                ['title' => $video['title']],
                $video
            );
        }
    }
}
