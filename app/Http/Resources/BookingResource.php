<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'booking_code'   => $this->booking_code,
            'bride_names'    => $this->bride_names,
            'initials'       => $this->initials,
            'event_date'     => $this->event_date?->format('Y-m-d'),
            'event_date_formatted' => $this->event_date?->locale('id')->translatedFormat('d F Y'),
            'event_type'     => $this->event_type,
            'decoration_type' => $this->decoration_type,
            'phone'          => $this->phone,
            'address'        => $this->address,
            'lat'            => (float) $this->lat,
            'lng'            => (float) $this->lng,
            'maps_url'       => $this->maps_url,
            'package_type'   => $this->package_type,
            'dp_amount'      => (float) $this->dp_amount,
            'total_amount'   => $this->total_amount ? (float) $this->total_amount : null,
            'payment_status' => $this->payment_status,
            'status'         => $this->status,
            'notes'          => $this->notes,
            'user'           => $this->whenLoaded('user', fn () => [
                'id'    => $this->user->id,
                'name'  => $this->user->name,
                'email' => $this->user->email,
            ]),
            'reminder_logs'  => $this->whenLoaded('reminderLogs', fn () =>
                $this->reminderLogs->map(fn ($log) => [
                    'type'    => $log->type,
                    'method'  => $log->method,
                    'sent_at' => $log->sent_at,
                ])
            ),
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
