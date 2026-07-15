<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Conversation extends Model
{
    protected $fillable = [
        'client_id',
        'worker_id',
        'last_message_at',
    ];

    protected $casts = [
        'last_message_at' => 'datetime',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function worker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'worker_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    public function latestMessage(): HasOne
    {
        return $this->hasOne(Message::class)->latestOfMany();
    }

    public static function findOrCreateForPair(int $clientId, int $workerId): self
    {
        return static::firstOrCreate([
            'client_id' => $clientId,
            'worker_id' => $workerId,
        ]);
    }

    public function otherUser(int $userId): ?User
    {
        if ($this->client_id === $userId) {
            return $this->worker;
        }
        if ($this->worker_id === $userId) {
            return $this->client;
        }
        return null;
    }
}
