<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SuspensionAppeal extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'grounds_of_appeal',
        'supporting_document_path',
        'status',
        'admin_decision_notes',
        'decided_at',
    ];

    protected $casts = [
        'decided_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

