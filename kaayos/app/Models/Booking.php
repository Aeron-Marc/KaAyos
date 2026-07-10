<?php

namespace App\Models;

use App\Exceptions\BookingStateException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\DB;

class Booking extends Model
{
    protected $fillable = [
        'booking_ref',
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
        'reschedule_requested_by',
        'reschedule_proposed_at',
        'reschedule_reason',
        'reschedule_status',
        'reschedule_responded_at',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $booking) {
            if (empty($booking->booking_ref)) {
                $date = now()->format('Ymd');
                $last = self::whereDate('created_at', today())->count();
                $booking->booking_ref = 'BK-' . $date . '-' . str_pad($last + 1, 5, '0', STR_PAD_LEFT);
            }
        });
    }

    const STATUS_NEW        = 'new';
    const STATUS_ACCEPTED    = 'accepted';
    const STATUS_EN_ROUTE    = 'en_route';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_COMPLETED   = 'completed';
    const STATUS_CANCELLED   = 'cancelled';

    const STATUSES = [
        self::STATUS_NEW,
        self::STATUS_ACCEPTED,
        self::STATUS_EN_ROUTE,
        self::STATUS_IN_PROGRESS,
        self::STATUS_COMPLETED,
        self::STATUS_CANCELLED,
    ];

    const STATUS_FLOW = [
        self::STATUS_NEW        => self::STATUS_ACCEPTED,
        self::STATUS_ACCEPTED   => self::STATUS_EN_ROUTE,
        self::STATUS_EN_ROUTE   => self::STATUS_IN_PROGRESS,
        self::STATUS_IN_PROGRESS => self::STATUS_COMPLETED,
    ];

    protected $casts = [
        'client_id'             => 'integer',
        'worker_id'             => 'integer',
        'scheduled_at'          => 'datetime',
        'completed_at'          => 'datetime',
        'cancelled_at'          => 'datetime',
        'price'                 => 'decimal:2',
        'reschedule_proposed_at' => 'datetime',
        'reschedule_responded_at' => 'datetime',
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

    public function photos(): HasMany
    {
        return $this->hasMany(BookingPhoto::class);
    }

    public function rescheduleRequestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reschedule_requested_by');
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

    public function scopeCancelled($query)
    {
        return $query->where('status', self::STATUS_CANCELLED);
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

        DB::transaction(function () use ($nextStatus) {
            $fresh = self::lockForUpdate()->findOrFail($this->id);

            if ($fresh->status !== $this->getOriginal('status')) {
                throw new BookingStateException(
                    "This booking was already updated to '{$fresh->status}' by another action. Please refresh and try again."
                );
            }

            $fresh->status = $nextStatus;

            if ($nextStatus === self::STATUS_COMPLETED) {
                $fresh->completed_at = now();
            }

            $fresh->save();

            $this->status = $fresh->status;
            $this->completed_at = $fresh->completed_at;
        });
    }
}