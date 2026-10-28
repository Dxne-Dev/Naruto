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
        Schema::table('tournaments', function (Blueprint $table) {
            if (! Schema::hasColumn('tournaments', 'is_private')) {
                $table->boolean('is_private')->default(false)->after('title');
            }
            if (! Schema::hasColumn('tournaments', 'room_code')) {
                $table->string('room_code', 16)->nullable()->unique()->after('is_private');
            }
            if (! Schema::hasColumn('tournaments', 'creator_id')) {
                $table->foreignId('creator_id')->nullable()->after('room_code')->constrained('users')->nullOnDelete();
            }
            if (! Schema::hasColumn('tournaments', 'max_participants')) {
                $table->unsignedTinyInteger('max_participants')->default(8)->after('creator_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tournaments', function (Blueprint $table) {
            if (Schema::hasColumn('tournaments', 'creator_id')) {
                $table->dropForeign(['creator_id']);
                $table->dropColumn('creator_id');
            }
            if (Schema::hasColumn('tournaments', 'room_code')) {
                $table->dropColumn('room_code');
            }
            if (Schema::hasColumn('tournaments', 'is_private')) {
                $table->dropColumn('is_private');
            }
        });
    }
};
