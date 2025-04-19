<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Agent extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $fillable = ['name', 'email', 'phone_number', 'bio', 'profile_picture'];

    public function conversationsWithUsers(): MorphToMany
    {
        return $this->morphToMany(User::class, 'agent', 'conversations');
    }

    public function messages(): MorphMany
    {
        return $this->morphMany(Message::class, 'sender');
    }

    public function properties(): HasMany
    {
        return $this->hasMany(Property::class);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('profile_picture')->single();
    }
}