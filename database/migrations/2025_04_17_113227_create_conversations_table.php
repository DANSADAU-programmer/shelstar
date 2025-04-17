<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('conversations', function (Blueprint $table) {
            $table->id();
            $table->morphs('user'); // The user involved in the conversation
            $table->morphs('agent'); // The agent involved in the conversation
            $table->timestamps();
            $table->unique(['user_id', 'user_type', 'agent_id', 'agent_type']); // Ensure unique conversations
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conversations');
    }
};
