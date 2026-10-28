<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WarrantyClaim extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'client_id',
        'worker_id',
        'claim_reason',
        'evidence_photo_path',
        'status',
        'warranty_valid_until',
        'resolution_notes',
    ];

    protected $casts = [
        'warranty_valid_until' => 'datetime',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function worker()
    {
        return $this->belongsTo(User::class, 'worker_id');
    }
}

