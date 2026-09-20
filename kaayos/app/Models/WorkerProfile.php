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
        'tools_equipped',
        'recommended_peers',
        'tesda_certified',
        'barangay_clearance_verified',
        'min_notice_hours',
        'emergency_available',
        'government_id_verified',
        'average_rating',
        'current_latitude',
        'current_longitude',
        'location_is_approximate',
    ];

    protected $casts = [
        'skills'                => 'array',
        'tools_equipped'        => 'array',
        'recommended_peers'     => 'array',
        'spoken_languages'      => 'array',
        'service_areas'         => 'array',
        'service_zone'          => 'array',
        'availability'          => 'array',
        'hourly_rate'           => 'decimal:2',
        'years_of_experience'   => 'integer',
        'min_notice_hours'      => 'integer',
        'service_radius'        => 'integer',
        'service_radius_km'     => 'integer',
        'government_id_verified'=> 'boolean',
        'tesda_certified'       => 'boolean',
        'barangay_clearance_verified' => 'boolean',
        'emergency_available'   => 'boolean',
        'average_rating'        => 'decimal:2',
        'current_latitude'      => 'decimal:7',
        'current_longitude'     => 'decimal:7',
        'location_is_approximate'=> 'boolean',
    ];

    public function getRecommendedPeersDetailsAttribute(): array
    {
        if (empty($this->recommended_peers) || !is_array($this->recommended_peers)) {
            return [];
        }

        $peerIds = collect($this->recommended_peers)->pluck('worker_id')->filter()->all();
        $workers = User::whereIn('id', $peerIds)
            ->active()
            ->with('workerProfile')
            ->get()
            ->keyBy('id');

        $result = [];
        foreach ($this->recommended_peers as $rec) {
            $peerId = $rec['worker_id'] ?? null;
            if ($peerId && isset($workers[$peerId])) {
                $w = $workers[$peerId];
                $result[] = [
                    'id'               => $w->id,
                    'name'             => $w->name,
                    'service_category' => $w->service_category,
                    'rating'           => $w->workerProfile?->average_rating ?? 0,
                    'avatar'           => $w->avatar ? \Illuminate\Support\Facades\Storage::url($w->avatar) : null,
                    'initials'         => strtoupper(substr($w->first_name, 0, 1) . substr($w->last_name, 0, 1)),
                    'note'             => $rec['note'] ?? $rec['endorsement'] ?? '',
                ];
            }
        }

        return $result;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function portfolios(): HasMany
    {
        return $this->hasMany(WorkPortfolio::class);
    }

    public function setServiceRadiusAttribute($value): void
    {
        $this->attributes['service_radius'] = $value;
        $this->attributes['service_radius_km'] = $value;
    }

    public function setServiceRadiusKmAttribute($value): void
    {
        $this->attributes['service_radius_km'] = $value;
        $this->attributes['service_radius'] = $value;
    }
}
