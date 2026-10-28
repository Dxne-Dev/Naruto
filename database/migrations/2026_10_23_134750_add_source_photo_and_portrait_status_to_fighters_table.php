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
        Schema::table('fighters', function (Blueprint $table) {
            $table->string('source_photo_path')->nullable()->after('talent');
            $table->string('portrait_status')->default('none')->after('portrait_path'); // 'none', 'pending', 'ready'
            $table->timestamp('portrait_ordered_at')->nullable()->after('portrait_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fighters', function (Blueprint $table) {
            $table->dropColumn(['source_photo_path', 'portrait_status', 'portrait_ordered_at']);
        });
    }
};
