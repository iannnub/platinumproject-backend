<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Booking extends Model
{
    protected $fillable = [
        'user_id',
        'booking_code',
        'bride_names',
        'initials',
        'event_date',
        'event_type',
        'decoration_type',
        'phone',
        'address',
        'lat',
        'lng',
        'maps_url',
        'package_type',
        'dp_amount',
        'total_amount',
        'payment_status',
        'status',
        'notes',
    ];

    protected $casts = [
        'event_date'    => 'date',
        'lat'           => 'decimal:8',
        'lng'           => 'decimal:8',
        'dp_amount'     => 'decimal:2',
        'total_amount'  => 'decimal:2',
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reminderLogs(): HasMany
    {
        return $this->hasMany(ReminderLog::class);
    }

    // Helpers
    public function isReminded(string $type): bool
    {
        return $this->reminderLogs()->where('type', $type)->exists();
    }
}
