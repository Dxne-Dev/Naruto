<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'is_bot')) {
                $table->boolean('is_bot')->default(false)->after('role')->index();
            }
        });

        // Retroactively flag existing injected bot accounts as bots in users and fighters tables
        DB::table('users')
            ->where('email', 'like', '%@arena.local')
            ->orWhere('username', 'like', 'Bot_%')
            ->orWhere('name', 'like', 'IA Bot_%')
            ->update(['is_bot' => true]);

        // Sync corresponding fighters to is_bot = true
        $botUserIds = DB::table('users')->where('is_bot', true)->pluck('id');
        if ($botUserIds->isNotEmpty()) {
            DB::table('fighters')
                ->whereIn('user_id', $botUserIds)
                ->update(['is_bot' => true]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'is_bot')) {
                $table->dropColumn('is_bot');
            }
        });
    }
};
