<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ServiceExtra extends Model
{
    protected $fillable = [
        'service_id',
        'name',
        'suggested_cost',
        'is_active',
    ];

    protected $casts = [
        'suggested_cost' => 'decimal:2',
        'is_active'      => 'boolean',
    ];

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function bookingExtras(): HasMany
    {
        return $this->hasMany(BookingExtra::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
