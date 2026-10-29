<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Identify admin users to preserve
        $adminUserIds = DB::table('users')
            ->where('role', 'admin')
            ->orWhere('username', 'Dxne')
            ->pluck('id')
            ->toArray();

        // 2. Clear social & transactional relations for non-admin users
        if (Schema::hasTable('friendships')) {
            DB::table('friendships')
                ->whereNotIn('user_id', $adminUserIds)
                ->orWhereNotIn('friend_id', $adminUserIds)
                ->delete();
        }

        if (Schema::hasTable('challenges')) {
            DB::table('challenges')
                ->whereNotIn('sender_id', $adminUserIds)
                ->orWhereNotIn('receiver_id', $adminUserIds)
                ->delete();
        }

        if (Schema::hasTable('pass_requests')) {
            DB::table('pass_requests')
                ->whereNotIn('user_id', $adminUserIds)
                ->delete();
        }

        if (Schema::hasTable('ip_tournament_quotas')) {
            DB::table('ip_tournament_quotas')->truncate();
        }

        // 3. Purge fighters that do not belong to admin users (and all bots/stale fighters)
        if (Schema::hasTable('fighters')) {
            $nonAdminFighterIds = DB::table('fighters')
                ->whereNotIn('user_id', $adminUserIds)
                ->orWhereNull('user_id')
                ->pluck('id')
                ->toArray();

            if (! empty($nonAdminFighterIds)) {
                if (Schema::hasTable('fighter_jutsu')) {
                    DB::table('fighter_jutsu')->whereIn('fighter_id', $nonAdminFighterIds)->delete();
                }

                if (Schema::hasTable('xp_transactions')) {
                    DB::table('xp_transactions')->whereIn('fighter_id', $nonAdminFighterIds)->delete();
                }

                if (Schema::hasTable('tournament_entries')) {
                    DB::table('tournament_entries')->whereIn('fighter_id', $nonAdminFighterIds)->delete();
                }

                if (Schema::hasTable('matches')) {
                    DB::table('matches')
                        ->whereIn('fighter_a_id', $nonAdminFighterIds)
                        ->orWhereIn('fighter_b_id', $nonAdminFighterIds)
                        ->delete();
                }

                if (Schema::hasTable('battles')) {
                    $battleIds = DB::table('battles')
                        ->whereIn('fighter_a_id', $nonAdminFighterIds)
                        ->orWhereIn('fighter_b_id', $nonAdminFighterIds)
                        ->pluck('id')
                        ->toArray();

                    if (! empty($battleIds) && Schema::hasTable('battle_logs')) {
                        DB::table('battle_logs')->whereIn('battle_id', $battleIds)->delete();
                    }

                    DB::table('battles')->whereIn('id', $battleIds)->delete();
                }

                DB::table('fighters')->whereIn('id', $nonAdminFighterIds)->delete();
            }
        }

        // 4. Delete all non-admin users
        DB::table('users')
            ->where('role', '!=', 'admin')
            ->where('username', '!=', 'Dxne')
            ->delete();

        // 5. Clean stale active tournaments without entries or reset them to clean slate
        if (Schema::hasTable('tournaments')) {
            DB::table('tournaments')->where('status', 'running')->update(['status' => 'completed']);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // irreversible purge
    }
};
