<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookingMaterial extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'worker_id',
        'item_name',
        'quantity',
        'unit',
        'unit_cost',
        'total_cost',
        'receipt_photo_path',
    ];

    protected $casts = [
        'quantity'   => 'decimal:2',
        'unit_cost'  => 'decimal:2',
        'total_cost' => 'decimal:2',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function worker()
    {
        return $this->belongsTo(User::class, 'worker_id');
    }
}

