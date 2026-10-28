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
        // 1. Update Users table with wheel tokens if not present
        if (! Schema::hasColumn('users', 'wheel_tokens')) {
            Schema::table('users', function (Blueprint $table) {
                $table->unsignedInteger('wheel_tokens')->default(3)->after('role');
            });
        }

        // 2. Update Fighters table for Naruto & Boruto Lore
        Schema::table('fighters', function (Blueprint $table) {
            if (! Schema::hasColumn('fighters', 'clan')) {
                $table->string('clan')->default('Sans Clan')->after('archetype');
            }
            if (! Schema::hasColumn('fighters', 'village')) {
                $table->string('village')->default('Konoha')->after('clan');
            }
            if (! Schema::hasColumn('fighters', 'ninja_rank')) {
                $table->string('ninja_rank')->default('Genin')->after('village');
            }
            if (! Schema::hasColumn('fighters', 'primary_element')) {
                $table->string('primary_element')->default('Katon')->after('ninja_rank');
            }
            if (! Schema::hasColumn('fighters', 'secondary_element')) {
                $table->string('secondary_element')->nullable()->after('primary_element');
            }
            if (! Schema::hasColumn('fighters', 'specialization')) {
                $table->string('specialization')->default('Ninjutsu')->after('secondary_element');
            }

            // Databook Stats (1.0 to 5.0 stored as decimal(3, 1))
            if (! Schema::hasColumn('fighters', 'stat_nin')) {
                $table->decimal('stat_nin', 3, 1)->default(2.5)->after('specialization');
            }
            if (! Schema::hasColumn('fighters', 'stat_tai')) {
                $table->decimal('stat_tai', 3, 1)->default(2.5)->after('stat_nin');
            }
            if (! Schema::hasColumn('fighters', 'stat_gen')) {
                $table->decimal('stat_gen', 3, 1)->default(2.5)->after('stat_tai');
            }
            if (! Schema::hasColumn('fighters', 'stat_int')) {
                $table->decimal('stat_int', 3, 1)->default(2.5)->after('stat_gen');
            }
            if (! Schema::hasColumn('fighters', 'stat_str')) {
                $table->decimal('stat_str', 3, 1)->default(2.5)->after('stat_int');
            }
            if (! Schema::hasColumn('fighters', 'stat_spd')) {
                $table->decimal('stat_spd', 3, 1)->default(2.5)->after('stat_str');
            }
            if (! Schema::hasColumn('fighters', 'stat_end')) {
                $table->decimal('stat_end', 3, 1)->default(2.5)->after('stat_spd');
            }
            if (! Schema::hasColumn('fighters', 'stat_sea')) {
                $table->decimal('stat_sea', 3, 1)->default(2.5)->after('stat_end');
            }

            if (! Schema::hasColumn('fighters', 'available_stat_points')) {
                $table->decimal('available_stat_points', 3, 1)->default(0.0)->after('xp');
            }
        });

        // 3. Create Jutsus Catalog Table
        if (! Schema::hasTable('jutsus')) {
            Schema::create('jutsus', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('type'); // Ninjutsu, Taijutsu, Genjutsu, Kekkei Genkai
                $table->string('element')->nullable(); // Katon, Futon, Raiton, Doton, Suiton
                $table->unsignedInteger('base_power');
                $table->unsignedInteger('chakra_cost');
                $table->string('rank')->default('C'); // E, D, C, B, A, S
                $table->string('clan_required')->nullable();
                $table->text('description')->nullable();
                $table->timestamps();
            });
        }

        // 4. Pivot Fighter - Jutsus
        if (! Schema::hasTable('fighter_jutsu')) {
            Schema::create('fighter_jutsu', function (Blueprint $table) {
                $table->id();
                $table->foreignId('fighter_id')->constrained()->cascadeOnDelete();
                $table->foreignId('jutsu_id')->constrained()->cascadeOnDelete();
                $table->timestamps();
            });
        }

        // 5. Update Tournaments table with max_participants
        if (! Schema::hasColumn('tournaments', 'max_participants')) {
            Schema::table('tournaments', function (Blueprint $table) {
                $table->unsignedInteger('max_participants')->default(8)->after('status');
            });
        }

        // 6. Update BattleLogs with full combat state
        Schema::table('battle_logs', function (Blueprint $table) {
            if (! Schema::hasColumn('battle_logs', 'chakra_remaining_a')) {
                $table->unsignedInteger('chakra_remaining_a')->default(0)->after('hp_remaining_b');
            }
            if (! Schema::hasColumn('battle_logs', 'chakra_remaining_b')) {
                $table->unsignedInteger('chakra_remaining_b')->default(0)->after('chakra_remaining_a');
            }
            if (! Schema::hasColumn('battle_logs', 'jutsu_used')) {
                $table->string('jutsu_used')->nullable()->after('chakra_remaining_b');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('battle_logs', function (Blueprint $table) {
            $table->dropColumn(['chakra_remaining_a', 'chakra_remaining_b', 'jutsu_used']);
        });

        Schema::table('tournaments', function (Blueprint $table) {
            $table->dropColumn('max_participants');
        });

        Schema::dropIfExists('fighter_jutsu');
        Schema::dropIfExists('jutsus');

        Schema::table('fighters', function (Blueprint $table) {
            $table->dropColumn([
                'clan', 'village', 'ninja_rank',
                'primary_element', 'secondary_element', 'specialization',
                'stat_nin', 'stat_tai', 'stat_gen', 'stat_int',
                'stat_str', 'stat_spd', 'stat_end', 'stat_sea',
                'available_stat_points',
            ]);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('wheel_tokens');
        });
    }
};
