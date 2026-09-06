<?php

namespace App\Console\Commands;

use App\Models\Booking;
use App\Models\ReminderLog;
use App\Services\WhatsAppService;
use Illuminate\Console\Command;

class SendBookingReminders extends Command
{
    protected $signature = 'bookings:send-reminders';
    protected $description = 'Process and log H-3 payment reminders and H-1 preparation reminders for bookings';

    public function handle(): int
    {
        $this->info('Starting booking reminders check...');

        // 1. Process H-3 Reminders (Pelunasan payment reminder)
        $h3Date = now()->addDays(3)->format('Y-m-d');
        $h3Bookings = Booking::whereDate('event_date', $h3Date)
            ->where('status', '!=', 'cancelled')
            ->where('payment_status', '!=', 'paid')
            ->get();

        $h3Count = 0;
        foreach ($h3Bookings as $booking) {
            $alreadySent = ReminderLog::where('booking_id', $booking->id)
                ->where('type', 'h3')
                ->exists();

            if (!$alreadySent) {
                $link = WhatsAppService::generateReminderLink($booking->phone, $booking, 'h3');

                ReminderLog::create([
                    'booking_id' => $booking->id,
                    'type'       => 'h3',
                    'method'     => 'wa',
                    'sent_at'    => now(),
                ]);

                $this->line("H-3 reminder logged for #{$booking->booking_code} ({$booking->bride_names}): {$link}");
                $h3Count++;
            }
        }

        // 2. Process H-1 Reminders (Setup & final confirmation)
        $h1Date = now()->addDays(1)->format('Y-m-d');
        $h1Bookings = Booking::whereDate('event_date', $h1Date)
            ->where('status', '!=', 'cancelled')
            ->get();

        $h1Count = 0;
        foreach ($h1Bookings as $booking) {
            $alreadySent = ReminderLog::where('booking_id', $booking->id)
                ->where('type', 'h1')
                ->exists();

            if (!$alreadySent) {
                $link = WhatsAppService::generateReminderLink($booking->phone, $booking, 'h1');

                ReminderLog::create([
                    'booking_id' => $booking->id,
                    'type'       => 'h1',
                    'method'     => 'wa',
                    'sent_at'    => now(),
                ]);

                $this->line("H-1 reminder logged for #{$booking->booking_code} ({$booking->bride_names}): {$link}");
                $h1Count++;
            }
        }

        $this->info("Completed reminders check: {$h3Count} H-3 and {$h1Count} H-1 reminders processed.");
        return Command::SUCCESS;
    }
}
