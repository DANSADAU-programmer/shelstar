<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany; // If using separate documents table


class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone_number', // Add phone number
        'bio',           // Add bio
        'profile_picture', // Add profile picture
        'location',      // Add location
        'website',       // Add website
        'date_of_birth', // Add date of birth
        'gender',        // Add gender
        'settings',      // Add settings (for JSON storage)
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'settings' => 'array', // Cast 'settings' column to an array
    ];

    public function conversationsWithAgents(): MorphToMany
    {
        return $this->morphToMany(Agent::class, 'user', 'conversations');
    }

    public function messages(): MorphMany
    {
        return $this->morphMany(Message::class, 'sender');
    }

    public function kycSubmission(): HasOne
    {
        return $this->hasOne(KYCSubmission::class);
    }

    // If using separate kyc_documents table
    public function kycDocuments(): HasMany
    {
        return $this->hasManyThrough(KYCDocument::class, KYCSubmission::class);
    }
}