<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PurgeNonAdminUsersCommand extends Command
{
    protected $signature = 'users:purge-non-admin';

    protected $description = 'Purge all non-admin users, their fighters, and related game data, preserving only admin Dxne';

    public function handle(): int
    {
        $this->info('Starting database purge...');

        // 1. Identify admin user IDs
        $adminUsers = DB::table('users')
            ->where('role', 'admin')
            ->orWhere('username', 'Dxne')
            ->get();

        $adminUserIds = $adminUsers->pluck('id')->toArray();

        $this->info('Preserved admin users: '.$adminUsers->pluck('username')->implode(', '));

        // 2. Clear social & transactional relations for non-admin users
        if (Schema::hasTable('friendships')) {
            $deleted = DB::table('friendships')
                ->whereNotIn('user_id', $adminUserIds)
                ->orWhereNotIn('friend_id', $adminUserIds)
                ->delete();
            $this->line("Deleted {$deleted} friendships.");
        }

        if (Schema::hasTable('challenges')) {
            $deleted = DB::table('challenges')
                ->whereNotIn('sender_id', $adminUserIds)
                ->orWhereNotIn('receiver_id', $adminUserIds)
                ->delete();
            $this->line("Deleted {$deleted} challenges.");
        }

        if (Schema::hasTable('pass_requests')) {
            $deleted = DB::table('pass_requests')
                ->whereNotIn('user_id', $adminUserIds)
                ->delete();
            $this->line("Deleted {$deleted} pass requests.");
        }

        if (Schema::hasTable('ip_tournament_quotas')) {
            DB::table('ip_tournament_quotas')->truncate();
            $this->line('Truncated ip_tournament_quotas.');
        }

        // 3. Purge fighters that do not belong to admin users
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

                $deletedFighters = DB::table('fighters')->whereIn('id', $nonAdminFighterIds)->delete();
                $this->line("Deleted {$deletedFighters} fighters.");
            }
        }

        // 4. Delete all non-admin users
        $deletedUsers = DB::table('users')
            ->where('role', '!=', 'admin')
            ->where('username', '!=', 'Dxne')
            ->delete();

        $this->line("Deleted {$deletedUsers} non-admin users.");

        // 5. Clean stale active tournaments
        if (Schema::hasTable('tournaments')) {
            DB::table('tournaments')->where('status', 'running')->update(['status' => 'completed']);
        }

        $remainingUsers = DB::table('users')->pluck('username')->implode(', ');
        $this->info("Purge complete! Remaining user(s): {$remainingUsers}");

        return Command::SUCCESS;
    }
}
