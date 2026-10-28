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
        Schema::create('battles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('match_id')->nullable()->constrained('matches')->cascadeOnDelete();
            $table->foreignId('fighter_a_id')->constrained('fighters')->cascadeOnDelete();
            $table->foreignId('fighter_b_id')->constrained('fighters')->cascadeOnDelete();
            $table->json('fighter_a_snapshot');
            $table->json('fighter_b_snapshot');
            $table->integer('fighter_a_final_hp');
            $table->integer('fighter_b_final_hp');
            $table->foreignId('winner_id')->nullable()->constrained('fighters')->nullOnDelete();
            $table->unsignedTinyInteger('rounds_count')->default(0);
            $table->string('random_seed');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('battles');
    }
};
