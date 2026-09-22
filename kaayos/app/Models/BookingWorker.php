<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingWorker extends Model
{
    use HasFactory;

    protected $table = 'booking_workers';

    protected $fillable = [
        'booking_id',
        'worker_id',
        'role',
        'payout_amount',
        'status',
        'justification_note',
        'invited_at',
        'responded_at',
    ];

    protected $casts = [
        'payout_amount' => 'decimal:2',
        'invited_at' => 'datetime',
        'responded_at' => 'datetime',
    ];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function worker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'worker_id');
    }

    public function isAccepted(): bool
    {
        return $this->status === 'accepted';
    }

    public function isPendingClientApproval(): bool
    {
        return $this->status === 'pending_client_approval';
    }
}

