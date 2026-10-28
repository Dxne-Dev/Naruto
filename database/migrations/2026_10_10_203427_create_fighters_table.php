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
        Schema::create('fighters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('archetype');
            $table->unsignedInteger('power')->default(10);
            $table->unsignedInteger('speed')->default(10);
            $table->unsignedInteger('defense')->default(10);
            $table->unsignedInteger('control')->default(10);
            $table->string('special_power');
            $table->string('talent');
            $table->string('portrait_path')->nullable();
            $table->unsignedInteger('level')->default(1);
            $table->unsignedInteger('xp')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fighters');
    }
};
