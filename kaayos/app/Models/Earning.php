<?php

namespace App\Models;

/**
 * Backward compatibility wrapper for BookingQuote.
 * Preserves legacy accessors so any unmigrated views/controllers don't break.
 */
class Earning extends BookingQuote
{
    protected $table = 'booking_quotes';

    protected $fillable = [
        'worker_id',
        'booking_id',
        'total_amount',
        'paid_at',
    ];

    public function getGrossAmountAttribute()
    {
        return $this->total_amount;
    }

    public function setGrossAmountAttribute($value)
    {
        $this->attributes['total_amount'] = $value;
    }

    public function getPlatformFeeAttribute()
    {
        return '0.00';
    }

    public function getNetAmountAttribute()
    {
        return $this->total_amount;
    }
}
