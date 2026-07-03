<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Earning extends Model
{
    protected $fillable = [
        'worker_id',
        'booking_id',
        'gross_amount',
        'platform_fee',
        'net_amount',
        'paid_at',
    ];

    protected $casts = [
        'gross_amount'  => 'decimal:2',
        'platform_fee'  => 'decimal:2',
        'net_amount'    => 'decimal:2',
        'paid_at'       => 'datetime',
    ];

    public function worker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'worker_id');
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }
}
