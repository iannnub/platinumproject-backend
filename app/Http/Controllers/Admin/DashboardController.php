<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    public function stats(): JsonResponse
    {
        $thisMonth = now()->startOfMonth();

        $totalBookings    = Booking::whereDate('created_at', '>=', $thisMonth)->count();
        $pendingBookings  = Booking::where('status', 'pending')->count();
        $confirmedBookings = Booking::where('status', 'confirmed')->count();
        $totalRevenue     = Booking::where('payment_status', 'paid')
                                ->whereDate('created_at', '>=', $thisMonth)
                                ->sum('total_amount');

        $recentBookings = Booking::with('user')
            ->orderByDesc('created_at')
            ->limit(10)
            ->get(['id', 'booking_code', 'bride_names', 'event_date', 'package_type', 'status', 'payment_status', 'created_at']);

        return response()->json([
            'success' => true,
            'data'    => [
                'total_bookings'     => $totalBookings,
                'pending_payment'    => $pendingBookings,
                'confirmed'          => $confirmedBookings,
                'stats' => [
                    'total_bookings'     => $totalBookings,
                    'pending_bookings'   => $pendingBookings,
                    'confirmed_bookings' => $confirmedBookings,
                    'total_revenue'      => (float) $totalRevenue,
                ],
                'recent_bookings' => $recentBookings,
            ],
        ]);
    }
}
