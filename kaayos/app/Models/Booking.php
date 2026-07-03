<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Booking extends Model
{
    protected $fillable = [
        'client_id',
        'worker_id',
        'service_category',
        'scheduled_at',
        'address',
        'notes',
        'status',
        'price',
        'completed_at',
        'cancelled_at',
        'cancellation_reason',
    ];

    const STATUS_NEW        = 'new';
    const STATUS_ACCEPTED    = 'accepted';
    const STATUS_EN_ROUTE    = 'en_route';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_COMPLETED   = 'completed';

    const STATUSES = [
        self::STATUS_NEW,
        self::STATUS_ACCEPTED,
        self::STATUS_EN_ROUTE,
        self::STATUS_IN_PROGRESS,
        self::STATUS_COMPLETED,
    ];

    const STATUS_FLOW = [
        self::STATUS_NEW        => self::STATUS_ACCEPTED,
        self::STATUS_ACCEPTED   => self::STATUS_EN_ROUTE,
        self::STATUS_EN_ROUTE   => self::STATUS_IN_PROGRESS,
        self::STATUS_IN_PROGRESS => self::STATUS_COMPLETED,
    ];

    protected $casts = [
        'client_id'     => 'integer',
        'worker_id'     => 'integer',
        'scheduled_at'  => 'datetime',
        'completed_at'  => 'datetime',
        'cancelled_at'  => 'datetime',
        'price'         => 'decimal:2',
    ];

    // ── Relationships ──────────────────────────────────────────

    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function worker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'worker_id');
    }

    public function earning(): HasOne
    {
        return $this->hasOne(Earning::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    public function review(): HasOne
    {
        return $this->hasOne(Review::class);
    }

    // ── Scopes ─────────────────────────────────────────────────

    public function scopeNew($query)
    {
        return $query->where('status', self::STATUS_NEW);
    }

    public function scopeAccepted($query)
    {
        return $query->where('status', self::STATUS_ACCEPTED);
    }

    public function scopeEnRoute($query)
    {
        return $query->where('status', self::STATUS_EN_ROUTE);
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', self::STATUS_IN_PROGRESS);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    public function scopeForClient($query, $clientId)
    {
        return $query->where('client_id', $clientId);
    }

    public function scopeForWorker($query, $workerId)
    {
        return $query->where('worker_id', $workerId);
    }

    // ── Helpers ────────────────────────────────────────────────

    public function isNew(): bool         { return $this->status === self::STATUS_NEW; }
    public function isAccepted(): bool    { return $this->status === self::STATUS_ACCEPTED; }
    public function isEnRoute(): bool     { return $this->status === self::STATUS_EN_ROUTE; }
    public function isInProgress(): bool  { return $this->status === self::STATUS_IN_PROGRESS; }
    public function isCompleted(): bool   { return $this->status === self::STATUS_COMPLETED; }

    public function isActive(): bool
    {
        return in_array($this->status, [
            self::STATUS_ACCEPTED,
            self::STATUS_EN_ROUTE,
            self::STATUS_IN_PROGRESS,
        ]);
    }

    public function canTransitionTo(string $nextStatus): bool
    {
        return isset(self::STATUS_FLOW[$this->status])
            && self::STATUS_FLOW[$this->status] === $nextStatus;
    }

    public function transitionTo(string $nextStatus): void
    {
        if (!$this->canTransitionTo($nextStatus)) {
            throw new \InvalidArgumentException(
                "Cannot transition from '{$this->status}' to '{$nextStatus}'."
            );
        }

        $this->status = $nextStatus;

        if ($nextStatus === self::STATUS_COMPLETED) {
            $this->completed_at = now();
        }

        $this->save();
    }
}