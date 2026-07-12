<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Message extends Model
{
    protected $fillable = [
        'booking_id',
        'sender_id',
        'receiver_id',
        'message',
        'read_at',
    ];

    protected $casts = [
        'read_at' => 'datetime',
    ];

    protected $touches = ['booking'];

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where(function ($q) use ($userId) {
            $q->where('sender_id', $userId)->orWhere('receiver_id', $userId);
        });
    }

    public function markAsRead(int $userId): void
    {
        if ($this->receiver_id === $userId && is_null($this->read_at)) {
            $this->update(['read_at' => now()]);
        }
    }

    public static function markAllAsReadForBooking(int $bookingId, int $userId): int
    {
        return static::where('booking_id', $bookingId)
            ->where('receiver_id', $userId)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }
}
