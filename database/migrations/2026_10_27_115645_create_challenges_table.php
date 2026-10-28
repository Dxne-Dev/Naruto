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
        Schema::create('challenges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sender_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('receiver_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('sender_fighter_id')->constrained('fighters')->cascadeOnDelete();
            $table->foreignId('receiver_fighter_id')->constrained('fighters')->cascadeOnDelete();
            $table->string('status')->default('pending'); // pending, accepted, declined, cancelled
            $table->foreignId('battle_id')->nullable()->constrained('battles')->nullOnDelete();
            $table->timestamp('responded_at')->nullable();
            $table->timestamps();

            $table->index(['receiver_id', 'status']);
            $table->index(['sender_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('challenges');
    }
};
