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
        Schema::create('battle_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('battle_id')->constrained('battles')->cascadeOnDelete();
            $table->unsignedTinyInteger('round_number');
            $table->foreignId('attacker_id')->constrained('fighters')->cascadeOnDelete();
            $table->string('action_type');
            $table->integer('damage_dealt')->default(0);
            $table->integer('hp_remaining_a');
            $table->integer('hp_remaining_b');
            $table->text('narrative');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('battle_logs');
    }
};
