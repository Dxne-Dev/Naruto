<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Purge ancient gamemaster user if present
        DB::table('users')->where('username', 'gamemaster')->orWhere('email', 'gm@otaku-arena.com')->delete();

        // 2. Convert any existing 'game_master' role in users table to 'admin'
        DB::table('users')->where('role', 'game_master')->update(['role' => 'admin']);

        // 3. Create or update the new Admin account Dxne
        $existing = DB::table('users')->where('username', 'Dxne')->orWhere('email', 'dxne@otaku-arena.com')->first();

        if ($existing) {
            DB::table('users')->where('id', $existing->id)->update([
                'username' => 'Dxne',
                'name' => 'Dxne Admin',
                'email' => 'dxne@otaku-arena.com',
                'password' => Hash::make('Secret123'),
                'role' => 'admin',
                'updated_at' => now(),
            ]);
        } else {
            DB::table('users')->insert([
                'username' => 'Dxne',
                'name' => 'Dxne Admin',
                'email' => 'dxne@otaku-arena.com',
                'password' => Hash::make('Secret123'),
                'role' => 'admin',
                'wheel_tokens' => 999,
                'has_paid_microtransaction' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('users')->where('username', 'Dxne')->orWhere('email', 'dxne@otaku-arena.com')->delete();
    }
};
