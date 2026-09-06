<?php

namespace App\Services;

class WhatsAppService
{
    private static array $adminNumbers = [
        '6285700751642', // Admin 1
        '6282325617934', // Admin 2
        '6285707752030', // Admin 3
    ];

    public static function generateAdminLinks($booking): array
    {
        $eventDate = \Carbon\Carbon::parse($booking->event_date)->locale('id')->translatedFormat('d F Y');
        $dpFormatted = number_format($booking->dp_amount, 0, ',', '.');
        $notesSection = $booking->notes ? "Catatan Tambahan:\n{$booking->notes}\n\n" : "";

        $message = "*🎉 FORMAT BOOKING PLATINUM PROJECT*\n\n"
            . "Kode Booking: {$booking->booking_code}\n"
            . "Nama Lengkap Mempelai: {$booking->bride_names}\n"
            . "Initial Nama: {$booking->initials}\n"
            . "Tanggal Acara: {$eventDate}\n"
            . "Jenis Acara: {$booking->event_type}\n"
            . "Dekor Akad: {$booking->decoration_type}\n"
            . "No WA: {$booking->phone}\n"
            . "Alamat: {$booking->address}\n"
            . "Jenis Paket: {$booking->package_type}\n"
            . "DP Minimal: Rp {$dpFormatted}\n\n"
            . "📍 Lokasi Google Maps:\n{$booking->maps_url}\n\n"
            . $notesSection
            . "PELUNASAN maksimal H - 1 SEBELUM HARI H ( dekor tidak dipasang jika belum melakukan pelunasan )";

        $encoded = rawurlencode($message);

        return array_map(function ($phone, $index) use ($encoded) {
            return [
                'admin' => 'Admin ' . ($index + 1),
                'phone' => $phone,
                'url'   => "https://wa.me/{$phone}?text={$encoded}",
            ];
        }, self::$adminNumbers, array_keys(self::$adminNumbers));
    }

    public static function generateReminderLink(string $phone, $booking, string $type): string
    {
        $eventDate = \Carbon\Carbon::parse($booking->event_date)->locale('id')->translatedFormat('d F Y');
        $h1Date    = \Carbon\Carbon::parse($booking->event_date)->subDay()->locale('id')->translatedFormat('d F Y');

        if ($type === 'h3') {
            $message = "Halo {$booking->bride_names},\n\n"
                . "Reminder: Acara Anda pada tanggal {$eventDate} tinggal 3 hari lagi! 🎊\n\n"
                . "Mohon segera melakukan *pelunasan* maksimal H-1 (sebelum {$h1Date}).\n"
                . "Dekorasi tidak akan dipasang jika belum lunas.\n\n"
                . "Kode Booking: {$booking->booking_code}\n"
                . "Paket: {$booking->package_type}\n\n"
                . "Terima kasih!\n"
                . "*Platinum Project* 💍";
        } else {
            $message = "Halo {$booking->bride_names},\n\n"
                . "Konfirmasi: Acara Anda *besok, {$eventDate}*! 🎊\n\n"
                . "Tim kami akan datang untuk setup dekorasi.\n"
                . "Mohon pastikan lokasi sudah siap.\n\n"
                . "📍 Lokasi: {$booking->address}\n"
                . "{$booking->maps_url}\n\n"
                . "Hubungi kami jika ada perubahan mendadak.\n"
                . "Terima kasih!\n"
                . "*Platinum Project* 💍";
        }

        return "https://wa.me/{$phone}?text=" . rawurlencode($message);
    }
}
