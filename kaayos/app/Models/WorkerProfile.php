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
        'service_zone',
        'cover_photo',
    ];

    protected $casts = [
        'skills'             => 'array',
        'spoken_languages'   => 'array',
        'service_areas'      => 'array',
        'service_zone'       => 'array',
        'hourly_rate'        => 'decimal:2',
        'years_of_experience'=> 'integer',
        'service_radius'     => 'integer',
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
