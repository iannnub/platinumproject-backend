<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Package;
use App\Models\User;
use Database\Seeders\AdminSeeder;
use Database\Seeders\PackageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PackageSeeder::class);
        $this->seed(AdminSeeder::class);
    }

    public function test_health_check_endpoint(): void
    {
        $response = $this->getJson('/api/health');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'status'  => 'ok',
            ]);
    }

    public function test_get_packages_returns_13_packages(): void
    {
        $response = $this->getJson('/api/packages');

        $response->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonCount(13, 'data');
    }

    public function test_guest_can_create_booking_and_receives_whatsapp_links(): void
    {
        $payload = [
            'bride_names'     => 'Anisa Rahma dan Dimas Anggara',
            'initials'        => 'A & D',
            'event_date'      => now()->addDays(14)->format('Y-m-d'),
            'event_type'      => 'Wedding',
            'decoration_type' => 'Dalam',
            'phone'           => '081234567890',
            'package_type'    => 'LITE',
            'dp_amount'       => 1000000,
            'lat'             => -8.409518,
            'lng'             => 115.188919,
            'address'         => 'Jl. Danau Tamblingan No. 25, Sanur, Denpasar, Bali',
            'notes'           => 'Tema warna White & Gold',
        ];

        $response = $this->postJson('/api/bookings', $payload);

        $response->assertStatus(201)
            ->assertJson(['success' => true])
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'booking_code',
                    'bride_names',
                    'event_date',
                    'maps_url',
                ],
                'wa_links' => [
                    '*' => ['admin', 'phone', 'url'],
                ],
            ]);

        $this->assertDatabaseHas('bookings', [
            'bride_names' => 'Anisa Rahma dan Dimas Anggara',
            'phone'       => '081234567890',
        ]);
    }

    public function test_admin_can_login_with_valid_credentials(): void
    {
        $response = $this->postJson('/api/admin/login', [
            'email'    => 'admin@platinumproject.my.id',
            'password' => 'PlatinumAdmin@2026',
        ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonStructure(['token', 'data']);
    }

    public function test_admin_login_fails_with_invalid_credentials(): void
    {
        $response = $this->postJson('/api/admin/login', [
            'email'    => 'admin@platinumproject.my.id',
            'password' => 'WrongPassword123',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function test_authenticated_admin_can_view_stats_and_bookings(): void
    {
        $admin = User::where('role', 'admin')->first();
        $token = $admin->createToken('test-token')->plainTextToken;

        // 1. Stats endpoint
        $statsRes = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/admin/dashboard/stats');

        $statsRes->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'stats' => ['total_bookings', 'pending_bookings', 'confirmed_bookings', 'total_revenue'],
                    'recent_bookings',
                ],
            ]);

        // 2. Bookings list endpoint
        $bookingsRes = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/admin/bookings');

        $bookingsRes->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data',
                'meta' => ['current_page', 'last_page', 'total'],
            ]);
    }
}
