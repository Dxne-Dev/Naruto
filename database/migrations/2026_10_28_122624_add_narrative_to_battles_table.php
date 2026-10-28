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
        Schema::table('battles', function (Blueprint $table) {
            if (! Schema::hasColumn('battles', 'ai_commentary')) {
                $table->longText('ai_commentary')->nullable()->after('fighter_b_final_hp');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('battles', function (Blueprint $table) {
            $table->dropColumn('ai_commentary');
        });
    }
};
