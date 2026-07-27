<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VerificationEvent extends Model
{
    protected $fillable = [
        'worker_verification_id',
        'event_type',
        'old_status',
        'new_status',
        'actor_id',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'json',
    ];

    public function verification(): BelongsTo
    {
        return $this->belongsTo(WorkerVerification::class, 'worker_verification_id');
    }

    public function actor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
