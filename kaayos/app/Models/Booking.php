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
        'house_no',
        'barangay',
        'agreed_by_client_at',
        'agreed_by_worker_at',
        'completion_requested_by',
        'completion_requested_at',
        'confirmed_by_worker_at',
        'confirmed_by_client_at',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $booking) {
            if (empty($booking->booking_ref)) {
                $date = now()->format('Ymd');
                $attempts = 0;
                do {
                    $suffix = $attempts > 0 ? chr(64 + $attempts) : '';
                    $last = self::whereDate('created_at', today())->count();
                    $booking->booking_ref = 'BK-' . $date . '-' . str_pad($last + 1, 4, '0', STR_PAD_LEFT) . $suffix;
                    $attempts++;
                    try {
                        $exists = self::where('booking_ref', $booking->booking_ref)->exists();
                    } catch (\Exception $e) {
                        break;
                    }
                } while ($exists && $attempts < 5);
                if ($exists ?? false) {
                    $booking->booking_ref = 'BK-' . $date . '-' . str_pad(self::max('id') + 1, 5, '0', STR_PAD_LEFT) . strtoupper(substr(md5(uniqid()), 0, 4));
                }
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
        'reschedule_proposed_at'  => 'datetime',
        'reschedule_responded_at' => 'datetime',
        'agreed_by_client_at'     => 'datetime',
        'agreed_by_worker_at'     => 'datetime',
        'completion_requested_by' => 'integer',
        'completion_requested_at' => 'datetime',
        'confirmed_by_worker_at'  => 'datetime',
        'confirmed_by_client_at'  => 'datetime',
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

    public function completionInitiator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'completion_requested_by');
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

    public function isCompletionPending(): bool
    {
        return $this->status === self::STATUS_IN_PROGRESS
            && $this->completion_requested_at !== null
            && ($this->confirmed_by_worker_at === null || $this->confirmed_by_client_at === null);
    }

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
        // Block direct transitions to COMPLETED - must use confirmCompletion() instead
        if ($nextStatus === self::STATUS_COMPLETED) {
            return false;
        }

        return isset(self::STATUS_FLOW[$this->status])
            && self::STATUS_FLOW[$this->status] === $nextStatus;
    }

    public function canMarkComplete(?int $userId = null): bool
    {
        // Job must be in progress and not already have a completion request pending
        if ($this->status !== self::STATUS_IN_PROGRESS) {
            return false;
        }

        // If no completion requested yet, either party can request
        if ($this->completion_requested_at === null) {
            return true;
        }

        // If completion already requested, only the other party can confirm
        if ($userId === null) {
            return false;
        }

        return $this->completion_requested_by !== $userId;
    }

    public function getCompletionStatus(): array
    {
        return [
            'is_pending' => $this->isCompletionPending(),
            'requested_by' => $this->completion_requested_by,
            'requested_at' => $this->completion_requested_at,
            'worker_confirmed' => $this->confirmed_by_worker_at !== null,
            'client_confirmed' => $this->confirmed_by_client_at !== null,
            'pending_from' => $this->getPendingConfirmationFrom(),
        ];
    }

    public function getPendingConfirmationFrom(): ?string
    {
        if ($this->confirmed_by_worker_at === null) {
            return 'worker';
        }
        if ($this->confirmed_by_client_at === null) {
            return 'client';
        }
        return null;
    }

    public function markComplete(User $user, ?\Closure $afterSave = null): void
    {
        if ($this->status !== self::STATUS_IN_PROGRESS) {
            throw new \InvalidArgumentException(
                "Cannot mark complete a booking that is not 'in_progress'."
            );
        }

        DB::transaction(function () use ($user, $afterSave) {
            $fresh = self::lockForUpdate()->findOrFail($this->id);

            // If no completion request yet, this user is initiating
            if ($fresh->completion_requested_at === null) {
                $fresh->completion_requested_by = $user->id;
                $fresh->completion_requested_at = now();

                // If user is worker, mark worker confirmed
                if ($user->id === $fresh->worker_id) {
                    $fresh->confirmed_by_worker_at = now();
                } else {
                    // User is client, mark client confirmed
                    $fresh->confirmed_by_client_at = now();
                }
            } else {
                // Completion already requested, this user is confirming
                if ($user->id === $fresh->completion_requested_by) {
                    throw new \InvalidArgumentException(
                        "You already marked this job as complete. Waiting for the other party to confirm."
                    );
                }

                // Mark this user's confirmation
                if ($user->id === $fresh->worker_id) {
                    $fresh->confirmed_by_worker_at = now();
                } else {
                    $fresh->confirmed_by_client_at = now();
                }
            }

            // If both parties confirmed, transition to completed
            if ($fresh->confirmed_by_worker_at !== null && $fresh->confirmed_by_client_at !== null) {
                $fresh->status = self::STATUS_COMPLETED;
                $fresh->completed_at = now();
            }

            $fresh->save();

            $fresh->history()->create([
                'user_id'    => $user->id,
                'old_status' => $this->status,
                'new_status' => $fresh->status,
                'notes'      => 'Completion confirmation by ' . ($user->id === $fresh->worker_id ? 'worker' : 'client'),
            ]);

            if ($afterSave) {
                $afterSave($fresh);
            }

            $this->status = $fresh->status;
            $this->completed_at = $fresh->completed_at;
            $this->completion_requested_by = $fresh->completion_requested_by;
            $this->completion_requested_at = $fresh->completion_requested_at;
            $this->confirmed_by_worker_at = $fresh->confirmed_by_worker_at;
            $this->confirmed_by_client_at = $fresh->confirmed_by_client_at;
            $this->syncOriginalAttribute('status');
        });
    }

    public function transitionTo(string $nextStatus, ?int $userId = null, ?\Closure $afterSave = null): void
    {
        if (!$this->canTransitionTo($nextStatus)) {
            throw new \InvalidArgumentException(
                "Cannot transition from '{$this->status}' to '{$nextStatus}'."
            );
        }

        DB::transaction(function () use ($nextStatus, $userId, $afterSave) {
            $fresh = self::lockForUpdate()->findOrFail($this->id);

            if ($fresh->status !== $this->getOriginal('status')) {
                throw new BookingStateException(
                    "This booking was already updated to '{$fresh->status}' by another action. Please refresh and try again."
                );
            }

            $oldStatus = $fresh->status;
            $fresh->status = $nextStatus;

            $fresh->save();

            $fresh->history()->create([
                'user_id'    => $userId,
                'old_status' => $oldStatus,
                'new_status' => $nextStatus,
            ]);

            if ($afterSave) {
                $afterSave($fresh);
            }

            $this->status = $fresh->status;
            $this->syncOriginalAttribute('status');
        });
    }

    public function cancel(?string $reason = null, ?int $userId = null): void
    {
        if (in_array($this->status, [self::STATUS_COMPLETED, self::STATUS_CANCELLED])) {
            throw new \InvalidArgumentException(
                "Cannot cancel a booking that is already '{$this->status}'."
            );
        }

        DB::transaction(function () use ($reason, $userId) {
            $fresh = self::lockForUpdate()->findOrFail($this->id);

            if ($fresh->status !== $this->getOriginal('status')) {
                throw new BookingStateException(
                    "This booking was already updated to '{$fresh->status}' by another action. Please refresh and try again."
                );
            }

            $oldStatus = $fresh->status;

            $fresh->update([
                'status'              => self::STATUS_CANCELLED,
                'cancelled_at'        => now(),
                'cancellation_reason' => $reason ?? 'Cancelled',
            ]);

            $fresh->history()->create([
                'user_id'    => $userId,
                'old_status' => $oldStatus,
                'new_status' => self::STATUS_CANCELLED,
                'notes'      => $reason,
            ]);

            $this->status = $fresh->status;
            $this->cancelled_at = $fresh->cancelled_at;
            $this->cancellation_reason = $fresh->cancellation_reason;
            $this->syncOriginalAttribute('status');
        });
    }

    public function history(): HasMany
    {
        return $this->hasMany(BookingHistory::class);
    }
}