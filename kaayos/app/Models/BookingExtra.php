<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingExtra extends Model
{
    protected $fillable = [
        'booking_id',
        'service_extra_id',
        'name',
        'cost',
        'source',
    ];

    protected $casts = [
        'cost' => 'decimal:2',
    ];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function serviceExtra(): BelongsTo
    {
        return $this->belongsTo(ServiceExtra::class);
    }

    public function isCustom(): bool
    {
        return $this->source === 'custom';
    }

    public function isCatalog(): bool
    {
        return $this->source === 'catalog';
    }
}
