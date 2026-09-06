<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBookingRequest;
use App\Http\Requests\UpdateBookingRequest;
use App\Http\Resources\BookingResource;
use App\Models\Booking;
use App\Services\BookingService;
use App\Services\WhatsAppService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    // ─── PUBLIC: Create booking ───────────────────────────────────
    public function store(StoreBookingRequest $request): JsonResponse
    {
        $data = $request->validated();

        // Add user_id if logged in
        if ($request->user()) {
            $data['user_id'] = $request->user()->id;
        }

        $booking = BookingService::create($data);

        // Generate WhatsApp links for all 3 admins
        $waLinks = WhatsAppService::generateAdminLinks($booking);

        return response()->json([
            'success' => true,
            'message' => 'Booking berhasil dibuat! Silakan konfirmasi via WhatsApp.',
            'data'    => new BookingResource($booking),
            'wa_links' => $waLinks,
        ], 201);
    }

    // ─── USER: Get own bookings ───────────────────────────────────
    public function myBookings(Request $request): JsonResponse
    {
        $bookings = Booking::where('user_id', $request->user()->id)
            ->orderByDesc('event_date')
            ->paginate(10);

        return response()->json([
            'success' => true,
            'data'    => BookingResource::collection($bookings),
            'meta'    => [
                'current_page' => $bookings->currentPage(),
                'last_page'    => $bookings->lastPage(),
                'per_page'     => $bookings->perPage(),
                'total'        => $bookings->total(),
            ],
        ]);
    }

    // ─── ADMIN: List all bookings (with filters) ──────────────────
    public function index(Request $request): JsonResponse
    {
        $query = Booking::query()->with('user');

        // Search
        if ($search = $request->search) {
            $query->where(function ($q) use ($search) {
                $q->where('bride_names', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('booking_code', 'like', "%{$search}%");
            });
        }

        // Filters
        if ($status = $request->status) {
            $query->where('status', $status);
        }
        if ($paymentStatus = $request->payment_status) {
            $query->where('payment_status', $paymentStatus);
        }
        if ($packageType = $request->package_type) {
            $query->where('package_type', $packageType);
        }

        // Date range
        if ($dateFrom = $request->date_from) {
            $query->where('event_date', '>=', $dateFrom);
        }
        if ($dateTo = $request->date_to) {
            $query->where('event_date', '<=', $dateTo);
        }

        $bookings = $query->orderByDesc('created_at')->paginate(20);

        return response()->json([
            'success' => true,
            'data'    => BookingResource::collection($bookings),
            'meta'    => [
                'current_page' => $bookings->currentPage(),
                'last_page'    => $bookings->lastPage(),
                'per_page'     => $bookings->perPage(),
                'total'        => $bookings->total(),
            ],
        ]);
    }

    // ─── ADMIN: Get single booking ────────────────────────────────
    public function show(Booking $booking): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data'    => new BookingResource($booking->load('user', 'reminderLogs')),
            'wa_links' => WhatsAppService::generateAdminLinks($booking),
        ]);
    }

    // ─── ADMIN: Update booking ────────────────────────────────────
    public function update(UpdateBookingRequest $request, Booking $booking): JsonResponse
    {
        $booking->update($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Booking berhasil diperbarui.',
            'data'    => new BookingResource($booking->fresh()),
        ]);
    }

    // ─── ADMIN: Delete booking ────────────────────────────────────
    public function destroy(Booking $booking): JsonResponse
    {
        $booking->delete();

        return response()->json([
            'success' => true,
            'message' => 'Booking berhasil dihapus.',
        ]);
    }
}
