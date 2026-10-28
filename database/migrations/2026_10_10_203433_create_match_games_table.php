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
        Schema::create('matches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tournament_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('round');
            $table->unsignedTinyInteger('match_number');
            $table->foreignId('fighter_a_id')->nullable()->constrained('fighters')->nullOnDelete();
            $table->foreignId('fighter_b_id')->nullable()->constrained('fighters')->nullOnDelete();
            $table->foreignId('winner_id')->nullable()->constrained('fighters')->nullOnDelete();
            $table->string('status')->default('pending');
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('matches');
    }
};
