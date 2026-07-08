<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkerProfile extends Model
{
    protected $fillable = [
        'user_id',
        'bio',
        'skills',
        'spoken_languages',
        'hourly_rate',
        'available_days',
        'preferred_hours',
        'service_areas',
        'years_of_experience',
        'service_radius',
        'service_radius_km',
        'service_zone',
        'availability',
        'cover_photo',
        'government_id_verified',
        'average_rating',
        'current_latitude',
        'current_longitude',
    ];

    protected $casts = [
        'skills'                => 'array',
        'spoken_languages'      => 'array',
        'service_areas'         => 'array',
        'service_zone'          => 'array',
        'availability'          => 'array',
        'hourly_rate'           => 'decimal:2',
        'years_of_experience'   => 'integer',
        'service_radius'        => 'integer',
        'service_radius_km'     => 'integer',
        'government_id_verified'=> 'boolean',
        'average_rating'        => 'decimal:2',
        'current_latitude'      => 'decimal:7',
        'current_longitude'     => 'decimal:7',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function portfolios(): HasMany
    {
        return $this->hasMany(WorkPortfolio::class);
    }

}
