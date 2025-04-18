<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KYCDocument extends Model
{
    use HasFactory;

    protected $fillable = ['kyc_submission_id', 'document_type', 'file_path'];

    public function kycSubmission(): BelongsTo
    {
        return $this->belongsTo(KYCSubmission::class);
    }
}