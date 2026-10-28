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
        Schema::create('tournament_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tournament_id')->constrained()->cascadeOnDelete();
            $table->foreignId('fighter_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('seed')->nullable();
            $table->unsignedTinyInteger('final_position')->nullable();
            $table->timestamp('registered_at')->useCurrent();
            $table->timestamps();

            $table->unique(['tournament_id', 'fighter_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tournament_entries');
    }
};
