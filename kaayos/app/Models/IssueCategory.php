<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class IssueCategory extends Model
{
    /** Trades a worker can have (matches the registration select). */
    public const TRADES = [
        'plumbing',
        'electrical',
        'carpentry',
        'painting',
        'aircon',
        'cleaning',
        'roofing',
        'welding',
        'gardening',
        'other',
    ];

    /** Concrete trades whose issue lists get grouped; 'other'/unknown see everything. */
    public const GROUPABLE_TRADES = [
        'plumbing',
        'electrical',
        'carpentry',
        'painting',
        'aircon',
        'cleaning',
        'roofing',
        'welding',
        'gardening',
    ];

    protected $fillable = [
        'name',
        'slug',
        'description',
        'icon',
        'service_category',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'issue_category_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
