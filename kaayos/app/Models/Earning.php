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
        'tip_amount',
        'net_amount',
        'paid_at',
    ];

    protected $casts = [
        'gross_amount'  => 'decimal:2',
        'platform_fee'  => 'decimal:2',
        'tip_amount'    => 'decimal:2',
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

    /**
     * Record (or refresh) the earning for a fully completed booking.
     * The platform fee is charged on the service price only — tips are passed through to the worker in full.
     */
    public static function recordForBooking(Booking $booking, ?int $workerId = null): self
    {
        $platformFeePercent = config('kaayos.platform_fee_percent', 10);
        $gross = (float) ($booking->price ?? 0);
        $tip = (float) ($booking->tip_amount ?? 0);
        $fee = round($gross * ($platformFeePercent / 100), 2);
        $net = $gross - $fee + $tip;

        return self::updateOrCreate(
            ['booking_id' => $booking->id],
            [
                'worker_id'    => $workerId ?? $booking->worker_id,
                'gross_amount' => $gross,
                'platform_fee' => $fee,
                'tip_amount'   => $tip,
                'net_amount'   => $net,
            ]
        );
    }
}
