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
        Schema::table('ip_tournament_quotas', function (Blueprint $table) {
            $table->dropUnique('ip_tournament_quotas_ip_address_unique');
            $table->index('ip_address');
            $table->string('device_fingerprint', 64)->nullable()->index()->after('ip_address');
            $table->text('user_agent')->nullable()->after('device_fingerprint');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('device_fingerprint', 64)->nullable()->index()->after('registration_ip');
        });

        Schema::table('tournament_entries', function (Blueprint $table) {
            $table->string('device_fingerprint', 64)->nullable()->index()->after('registration_ip');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tournament_entries', function (Blueprint $table) {
            $table->dropColumn('device_fingerprint');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('device_fingerprint');
        });

        Schema::table('ip_tournament_quotas', function (Blueprint $table) {
            $table->dropColumn(['device_fingerprint', 'user_agent']);
            $table->dropIndex(['ip_address']);
            $table->unique('ip_address');
        });
    }
};
