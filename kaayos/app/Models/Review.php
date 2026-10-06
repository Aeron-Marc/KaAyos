<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Review extends Model
{
    protected $fillable = [
        'booking_id',
        'client_id',
        'worker_id',
        'rating',
        'comment',
        'photo_path',
    ];

    protected $casts = [
        'rating' => 'integer',
    ];

    protected $appends = ['photo_url', 'edited'];

    public function getPhotoUrlAttribute(): ?string
    {
        return $this->photo_path ? Storage::url($this->photo_path) : null;
    }

    /**
     * Moment until which the client may still edit this review
     * (first submission time + configured grace period).
     */
    public function editableUntil(): CarbonInterface
    {
        return $this->created_at->copy()->addHours((int) config('kaayos.review_edit_grace_hours', 72));
    }

    public function canBeEdited(): bool
    {
        return $this->editableUntil()->isFuture();
    }

    public function getEditedAttribute(): bool
    {
        return $this->updated_at && $this->created_at && $this->updated_at->gt($this->created_at);
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function worker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'worker_id');
    }
}
