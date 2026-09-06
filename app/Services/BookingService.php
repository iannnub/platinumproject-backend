<?php

namespace App\Services;

class BookingService
{
    /**
     * Generate unique booking code: #PB[YYYYMMDD][XXX]
     */
    public static function generateCode(): string
    {
        $today    = now()->format('Ymd');
        $prefix   = "PB{$today}";

        // Count bookings created today for sequential number
        $count = \App\Models\Booking::whereDate('created_at', today())->count();
        $seq   = str_pad($count + 1, 3, '0', STR_PAD_LEFT);

        return "#{$prefix}{$seq}";
    }

    /**
     * Generate Google Maps URL from lat/lng
     */
    public static function generateMapsUrl(float $lat, float $lng): string
    {
        return "https://maps.google.com/?q={$lat},{$lng}";
    }

    /**
     * Create booking with all business logic
     */
    public static function create(array $data): \App\Models\Booking
    {
        $data['booking_code'] = self::generateCode();
        $data['maps_url']     = self::generateMapsUrl($data['lat'], $data['lng']);

        return \App\Models\Booking::create($data);
    }
}
