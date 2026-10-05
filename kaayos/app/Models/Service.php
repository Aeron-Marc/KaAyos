<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Service extends Model
{
    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'description',
        'base_price',
        'billing_type',
        'is_active',
    ];

    protected $casts = [
        'base_price' => 'decimal:2',
        'is_active'  => 'boolean',
    ];

    const BILLING_TYPES = ['fixed', 'hourly', 'either'];

    public function category(): BelongsTo
    {
        return $this->belongsTo(ServiceCategory::class, 'category_id');
    }

    public function providerServices(): HasMany
    {
        return $this->hasMany(ProviderService::class);
    }

    public function extras(): HasMany
    {
        return $this->hasMany(ServiceExtra::class);
    }

    public function activeExtras(): HasMany
    {
        return $this->hasMany(ServiceExtra::class)->where('is_active', true);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
