<?php
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

// ... other code ...

public function conversationsWithUsers(): MorphToMany
{
    return $this->morphToMany(User::class, 'agent', 'conversations');
}

public function messages(): MorphMany
{
    return $this->morphMany(Message::class, 'sender');
}