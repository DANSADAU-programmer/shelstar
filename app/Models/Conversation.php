<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Conversation extends Model
{
    protected $fillable = ['user_id', 'user_type', 'agent_id', 'agent_type'];

    public function user(): MorphTo
    {
        return $this->morphTo();
    }

    public function agent(): MorphTo
    {
        return $this->morphTo();
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class)->orderBy('created_at');
    }

    public function unreadMessages(int $recipientId, string $recipientType): HasMany
    {
        return $this->hasMany(Message::class)
            ->where('sender_id', '!=', $recipientId)
            ->where('sender_type', '!=', $recipientType)
            ->whereNull('read_at');
    }
}