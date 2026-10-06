<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class BookingMaterial extends Model
{
    protected $fillable = [
        'booking_id',
        'name',
        'qty',
        'unit_price',
        'line_total',
        'receipt_photo_path',
    ];

    protected $casts = [
        'qty'         => 'decimal:2',
        'unit_price'  => 'decimal:2',
        'line_total'  => 'decimal:2',
    ];

    protected $appends = ['receipt_url'];

    public function getReceiptUrlAttribute(): ?string
    {
        return $this->receipt_photo_path ? Storage::url($this->receipt_photo_path) : null;
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    /**
     * Persist a material line item, computing the server-side line total.
     */
    public static function computeLineTotal(float $qty, float $unitPrice): float
    {
        return round($qty * $unitPrice, 2);
    }

    /**
     * Recompute the booking's denormalized materials_total and re-sync the
     * earning (when the job is already completed).
     */
    public static function recalcForBooking(Booking $booking): void
    {
        $total = round(
            (float) $booking->materials()->sum('line_total'),
            2
        );

        if ((float) $booking->materials_total !== $total) {
            $booking->update(['materials_total' => $total]);
        } else {
            $booking->refresh();
        }

        if ($booking->status === Booking::STATUS_COMPLETED) {
            $booking->syncEarning();
        }
    }
}
