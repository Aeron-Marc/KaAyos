<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class ClientIdentityVerification extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_VERIFIED = 'verified';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_RESUBMISSION_REQUIRED = 'resubmission_required';

    protected $fillable = [
        'user_id',
        'id_type',
        'id_document_path',
        'selfie_path',
        'consent_at',
        'consent_version',
        'age_attested_at',
    ];

    protected $guarded = [
        'id',
        'status',
        'reviewed_by',
        'reviewed_at',
        'rejection_reason',
        'admin_notes',
        'files_purge_after',
        'files_purged_at',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'reviewed_at' => 'datetime',
        'consent_at' => 'datetime',
        'age_attested_at' => 'datetime',
        'files_purge_after' => 'datetime',
        'files_purged_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeVerified($query)
    {
        return $query->where('status', self::STATUS_VERIFIED);
    }

    public function scopeRejected($query)
    {
        return $query->where('status', self::STATUS_REJECTED);
    }

    public function scopeResubmissionRequired($query)
    {
        return $query->where('status', self::STATUS_RESUBMISSION_REQUIRED);
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isVerified(): bool
    {
        return $this->status === self::STATUS_VERIFIED;
    }

    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    public function needsResubmission(): bool
    {
        return $this->status === self::STATUS_RESUBMISSION_REQUIRED;
    }

    public function canBeReviewed(): bool
    {
        return $this->isPending();
    }

    public function canBeResubmittedBy($user): bool
    {
        return $this->user_id === $user->id
            && in_array($this->status, [self::STATUS_REJECTED, self::STATUS_RESUBMISSION_REQUIRED], true);
    }

    public function purgeFiles(): void
    {
        $disk = Storage::disk(config('client_identity.disk', 'identity'));

        foreach ([$this->id_document_path, $this->selfie_path] as $path) {
            if (! empty($path)) {
                $disk->delete($path);
            }
        }
    }
}
