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
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone_number')->nullable();
            $table->text('bio')->nullable();
            $table->string('profile_picture')->nullable();
            $table->string('location')->nullable();
            $table->string('website')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('gender')->nullable();
            $table->json('settings')->nullable(); // For storing settings as JSON
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('phone_number');
            $table->dropColumn('bio');
            $table->dropColumn('profile_picture');
            $table->dropColumn('location');
            $table->dropColumn('website');
            $table->dropColumn('date_of_birth');
            $table->dropColumn('gender');
            $table->dropColumn('settings');
        });
    }
};
