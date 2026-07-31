<?php

namespace Tests\Feature;

use App\Models\HomeVideo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomeVideosTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_loads_successfully_with_video_shorts(): void
    {
        // Seed category for frontpage dependencies
        $category = \App\Models\Category::create([
            'name' => 'Test Cat',
            'slug' => 'test-cat',
        ]);

        // Create sample active and inactive videos
        HomeVideo::create([
            'title' => 'Video Review 1',
            'description' => 'Aroma Majestic Oud wangi banget',
            'external_video_url' => 'https://assets.mixkit.co/videos/preview/mixkit-spinning-perfume-bottle-in-slow-motion-41857-large.mp4',
            'social_media_url' => 'https://instagram.com/joudahstore',
            'social_media_platform' => 'instagram',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        HomeVideo::create([
            'title' => 'Video Inactive 2',
            'description' => 'Aroma Bukhur',
            'external_video_url' => 'https://assets.mixkit.co/videos/preview/mixkit-hand-holding-a-perfume-bottle-and-spraying-it-41858-large.mp4',
            'social_media_url' => 'https://tiktok.com/@joudahstore',
            'social_media_platform' => 'tiktok',
            'is_active' => false,
            'sort_order' => 2,
        ]);

        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('Video Review 1');
        $response->assertSee('Aroma Majestic Oud wangi banget');
        $response->assertDontSee('Video Inactive 2');
    }

    public function test_guests_cannot_access_admin_home_videos_resource(): void
    {
        $response = $this->get('/admin/home-videos');
        $response->assertStatus(302); // Redirect to admin login
    }

    public function test_non_admin_users_cannot_access_admin_home_videos_resource(): void
    {
        $user = User::factory()->create([
            'is_admin' => 0,
        ]);

        $response = $this->actingAs($user)->get('/admin/home-videos');
        $response->assertStatus(403);
    }

    public function test_admin_users_can_access_admin_home_videos_resource(): void
    {
        $admin = User::factory()->create([
            'is_admin' => 1,
        ]);

        $response = $this->actingAs($admin)->get('/admin/home-videos');
        $response->assertStatus(200);
        $response->assertSee('Home Videos');
    }
}
