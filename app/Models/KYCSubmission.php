<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KYCSubmission extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'status', 'personal_information', 'rejection_reason', 'verification_level'];
    protected $casts = [
        'personal_information' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(KYCDocument::class);
    }
}