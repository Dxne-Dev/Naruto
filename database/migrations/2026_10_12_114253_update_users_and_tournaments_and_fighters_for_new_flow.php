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
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'has_paid_microtransaction')) {
                $table->boolean('has_paid_microtransaction')->default(false)->after('wheel_tokens');
            }
            if (! Schema::hasColumn('users', 'registration_ip')) {
                $table->string('registration_ip', 45)->nullable()->after('has_paid_microtransaction');
            }
            if (! Schema::hasColumn('users', 'is_suspended')) {
                $table->boolean('is_suspended')->default(false)->after('registration_ip');
            }
        });

        Schema::table('fighters', function (Blueprint $table) {
            if (! Schema::hasColumn('fighters', 'strategy_stance')) {
                $table->string('strategy_stance', 32)->default('balanced')->after('available_stat_points');
            }
            if (! Schema::hasColumn('fighters', 'jutsu_priority_type')) {
                $table->string('jutsu_priority_type', 32)->default('auto')->after('strategy_stance');
            }
            if (! Schema::hasColumn('fighters', 'is_bot')) {
                $table->boolean('is_bot')->default(false)->after('jutsu_priority_type');
            }
        });

        if (! Schema::hasColumn('tournament_entries', 'registration_ip')) {
            Schema::table('tournament_entries', function (Blueprint $table) {
                $table->string('registration_ip', 45)->nullable()->after('registered_at');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tournament_entries', function (Blueprint $table) {
            $table->dropColumn('registration_ip');
        });

        Schema::table('fighters', function (Blueprint $table) {
            $table->dropColumn(['strategy_stance', 'jutsu_priority_type', 'is_bot']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['has_paid_microtransaction', 'registration_ip', 'is_suspended']);
        });
    }
};
