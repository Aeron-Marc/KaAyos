<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientAddress extends Model
{
    protected $fillable = [
        'user_id',
        'label',
        'house_no',
        'barangay',
        'latitude',
        'longitude',
        'is_default',
    ];

    protected $casts = [
        'latitude'   => 'decimal:7',
        'longitude'  => 'decimal:7',
        'is_default' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function fullAddress(): string
    {
        return trim($this->house_no . ', Brgy. ' . $this->barangay . ', Tuy, Batangas');
    }
}
