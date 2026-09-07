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
        'total_paid',
        'remaining_amount',
        'payment_status',
        'status',
        'notes',
    ];

    protected $casts = [
        'event_date'       => 'date',
        'lat'              => 'decimal:8',
        'lng'              => 'decimal:8',
        'dp_amount'        => 'decimal:2',
        'total_amount'     => 'decimal:2',
        'total_paid'       => 'decimal:2',
        'remaining_amount' => 'decimal:2',
    ];

    // Existing relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reminderLogs(): HasMany
    {
        return $this->hasMany(ReminderLog::class);
    }

    // Financial relationships
    public function paymentLogs(): HasMany
    {
        return $this->hasMany(PaymentLog::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    // Computed attributes
    public function getTotalExpensesAttribute(): float
    {
        return (float) $this->expenses()->sum('amount');
    }

    // Cash Position = Total Paid - Total Expenses (Real cash flow)
    public function getCashPositionAttribute(): float
    {
        return (float) ($this->total_paid - $this->total_expenses);
    }

    // Expected Profit = Package Price (total_amount) - Total Expenses (Potential profit)
    public function getExpectedProfitAttribute(): float
    {
        if (!$this->total_amount) return 0;
        return (float) ($this->total_amount - $this->total_expenses);
    }

    // Profit Margin %
    public function getProfitMarginAttribute(): float
    {
        if (!$this->total_amount || $this->total_amount == 0) return 0;
        return (float) (($this->expected_profit / $this->total_amount) * 100);
    }

    // Helpers
    public function isReminded(string $type): bool
    {
        return $this->reminderLogs()->where('type', $type)->exists();
    }
}
