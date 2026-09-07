<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\Expense;
use App\Models\Package;
use App\Models\PaymentLog;
use App\Models\User;
use Database\Seeders\AdminSeeder;
use Database\Seeders\PackageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FinancialApiTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private string $adminToken;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PackageSeeder::class);
        $this->seed(AdminSeeder::class);

        $this->admin = User::where('email', 'admin@platinumproject.my.id')->first();
        $this->adminToken = $this->admin->createToken('test_token')->plainTextToken;
    }

    private function adminHeaders(): array
    {
        return [
            'Authorization' => 'Bearer ' . $this->adminToken,
            'Accept'        => 'application/json',
        ];
    }

    public function test_unauthenticated_user_cannot_access_financial_endpoints(): void
    {
        $response = $this->getJson('/api/admin/financial/dashboard');
        $response->assertStatus(401);
    }

    public function test_admin_can_access_financial_dashboard(): void
    {
        $response = $this->getJson('/api/admin/financial/dashboard', $this->adminHeaders());

        $response->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonStructure([
                'success',
                'data' => [
                    'period' => ['type', 'start', 'end'],
                    'summary' => [
                        'total_revenue',
                        'total_expenses',
                        'net_profit',
                        'expected_revenue',
                        'expected_profit',
                        'average_profit_margin',
                        'outstanding_payments',
                    ],
                    'booking_stats',
                ],
            ]);
    }

    public function test_admin_can_access_cash_flow_and_profit_by_package(): void
    {
        $flowRes = $this->getJson('/api/admin/financial/cash-flow?year=2026', $this->adminHeaders());
        $flowRes->assertStatus(200)
            ->assertJson(['success' => true])
            ->assertJsonStructure(['data' => ['year', 'months']]);

        $profitRes = $this->getJson('/api/admin/financial/profit-by-package', $this->adminHeaders());
        $profitRes->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    public function test_admin_can_record_dp_and_pelunasan_payment(): void
    {
        $booking = Booking::create([
            'booking_code'    => '#PB20260907001',
            'bride_names'     => 'Citra & Dimas',
            'initials'        => 'C & D',
            'address'         => 'Jl. Gatot Subroto No. 1, Denpasar',
            'lat'             => -8.65,
            'lng'             => 115.21,
            'maps_url'        => 'https://maps.google.com/?q=-8.65,115.21',
            'phone'           => '081234567890',
            'package_type'    => 'LITE',
            'event_date'      => '2026-10-10',
            'event_type'      => 'Wedding',
            'decoration_type' => 'Dalam',
            'total_amount'    => 5000000,
            'dp_amount'       => 1000000,
            'payment_status'  => 'pending',
            'total_paid'      => 0,
            'remaining_amount'=> 5000000,
        ]);

        // 1. Record DP (Rp 1.000.000)
        $dpPayload = [
            'booking_id'     => $booking->id,
            'payment_type'   => 'dp',
            'amount'         => 1000000,
            'payment_date'   => '2026-09-07',
            'payment_method' => 'transfer',
            'notes'          => 'DP transfer BCA',
        ];

        $dpRes = $this->postJson('/api/admin/financial/payment', $dpPayload, $this->adminHeaders());
        $dpRes->assertStatus(201)
            ->assertJson(['success' => true])
            ->assertJsonPath('data.booking.payment_status', 'dp_paid')
            ->assertJsonPath('data.booking.total_paid', 1000000)
            ->assertJsonPath('data.booking.remaining_amount', 4000000);

        // 2. Record Pelunasan (Rp 4.000.000)
        $pelunasanPayload = [
            'booking_id'     => $booking->id,
            'payment_type'   => 'pelunasan',
            'amount'         => 4000000,
            'payment_date'   => '2026-09-07',
            'payment_method' => 'transfer',
            'notes'          => 'Pelunasan transfer BCA',
        ];

        $pelunasanRes = $this->postJson('/api/admin/financial/payment', $pelunasanPayload, $this->adminHeaders());
        $pelunasanRes->assertStatus(201)
            ->assertJson(['success' => true])
            ->assertJsonPath('data.booking.payment_status', 'fully_paid')
            ->assertJsonPath('data.booking.total_paid', 5000000)
            ->assertJsonPath('data.booking.remaining_amount', 0);
    }

    public function test_admin_can_crud_expenses_and_calculations_update(): void
    {
        $booking = Booking::create([
            'booking_code'    => '#PB20260907002',
            'bride_names'     => 'Maya & Rio',
            'initials'        => 'M & R',
            'address'         => 'Jl. Sunset Road No. 88, Kuta',
            'lat'             => -8.70,
            'lng'             => 115.17,
            'maps_url'        => 'https://maps.google.com/?q=-8.70,115.17',
            'phone'           => '081234567891',
            'package_type'    => 'ROYAL',
            'event_date'      => '2026-11-15',
            'event_type'      => 'Wedding',
            'decoration_type' => 'Dalam',
            'total_amount'    => 15000000,
            'dp_amount'       => 1000000,
            'payment_status'  => 'dp_paid',
            'total_paid'      => 1000000,
            'remaining_amount'=> 14000000,
        ]);

        // 1. Add Expense
        $expensePayload = [
            'booking_id'   => $booking->id,
            'item_name'    => 'Sewa Bunga Mawar 500 tangkai',
            'description'  => 'Supplier Jaya Bunga',
            'amount'       => 2000000,
            'expense_date' => '2026-09-07',
            'notes'        => 'Free-text note',
        ];

        $addRes = $this->postJson('/api/admin/financial/expense', $expensePayload, $this->adminHeaders());
        $addRes->assertStatus(201)
            ->assertJson(['success' => true])
            ->assertJsonPath('data.item_name', 'Sewa Bunga Mawar 500 tangkai');

        $expenseId = $addRes->json('data.id');

        // 2. Check Booking detail calculation:
        // total_paid: 1.000.000, total_expenses: 2.000.000
        // cash_position = 1.000.000 - 2.000.000 = -1.000.000
        // expected_profit = 15.000.000 - 2.000.000 = 13.000.000
        $detailRes = $this->getJson("/api/admin/financial/booking/{$booking->id}", $this->adminHeaders());
        $detailRes->assertStatus(200)
            ->assertJsonPath('data.profit_analysis.cash_position.value', -1000000)
            ->assertJsonPath('data.profit_analysis.expected_profit.value', 13000000);

        // 3. Update Expense
        $updateRes = $this->putJson("/api/admin/financial/expense/{$expenseId}", [
            'amount' => 1500000,
        ], $this->adminHeaders());
        $updateRes->assertStatus(200)
            ->assertJson(['success' => true]);

        // 4. Delete Expense
        $delRes = $this->deleteJson("/api/admin/financial/expense/{$expenseId}", [], $this->adminHeaders());
        $delRes->assertStatus(200)
            ->assertJson(['success' => true]);
    }
}
