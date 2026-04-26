<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // ── 1. vaccine_types table ────────────────────────────────────────────
        Schema::create('vaccine_types', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->text('description')->nullable();
            $table->unsignedSmallInteger('interval_days')->nullable();
            $table->json('season_months')->nullable();
            $table->timestamps();
        });

        // ── 2. Add vaccine_type_id FK column (nullable during migration) ──────
        Schema::table('vaccines', function (Blueprint $table) {
            $table->unsignedBigInteger('vaccine_type_id')->nullable()->after('rfid_tag');
        });

        // ── 3. Map existing vaccine_type strings → vaccine_type_id ────────────
        // Seeder has not run yet here, so use DB::table with the names as keys.
        // VaccineTypeSeeder must run BEFORE this migration is executed on existing data.
        // On a fresh install the table is empty so this loop is a no-op.
        $typeMap = DB::table('vaccine_types')->pluck('id', 'name');
        foreach ($typeMap as $name => $id) {
            DB::table('vaccines')->where('vaccine_type', $name)->update(['vaccine_type_id' => $id]);
        }

        // Auto-create VaccineType entries for any unmatched legacy strings
        $unmatched = DB::table('vaccines')
            ->whereNull('vaccine_type_id')
            ->whereNotNull('vaccine_type')
            ->where('vaccine_type', '!=', '')
            ->pluck('vaccine_type')
            ->unique();

        $now = now()->toDateTimeString();
        foreach ($unmatched as $typeName) {
            $newId = DB::table('vaccine_types')->insertGetId([
                'name' => $typeName,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            DB::table('vaccines')
                ->where('vaccine_type', $typeName)
                ->whereNull('vaccine_type_id')
                ->update(['vaccine_type_id' => $newId]);
        }

        // ── 4. Drop all existing vaccine indexes before column manipulation ───
        $existing = collect(DB::select("PRAGMA index_list('vaccines')"))->pluck('name');
        $toDrop = [
            'vaccines_type_rfid_date_index',
            'vaccines_date_id_index',
            'vaccines_user_id_index',
            'vaccines_workstation_id_index',
            'vaccines_rfid_tag_index',
        ];
        foreach ($toDrop as $idx) {
            if ($existing->contains($idx)) {
                Schema::table('vaccines', fn(Blueprint $t) => $t->dropIndex($idx));
            }
        }

        // ── 5. Drop old vaccine_type string column ────────────────────────────
        Schema::table('vaccines', function (Blueprint $table) {
            $table->dropColumn('vaccine_type');
        });

        // ── 6. Recreate all performance indexes (now on vaccine_type_id) ──────
        Schema::table('vaccines', function (Blueprint $table) {
            $table->index('vaccine_type_id', 'vaccines_vaccine_type_id_index');
            $table->index(['vaccine_type_id', 'rfid_tag', 'vaccination_date'], 'vaccines_type_rfid_date_index');
            $table->index(['vaccination_date', 'id'], 'vaccines_date_id_index');
            $table->index('user_id', 'vaccines_user_id_index');
            $table->index('workstation_id', 'vaccines_workstation_id_index');
            $table->index('rfid_tag', 'vaccines_rfid_tag_index');
        });
    }

    public function down(): void
    {
        // 1. Re-add vaccine_type string column
        Schema::table('vaccines', function (Blueprint $table) {
            $table->string('vaccine_type')->nullable()->after('rfid_tag');
        });

        // 2. Populate from relation
        DB::statement('UPDATE vaccines SET vaccine_type = (SELECT name FROM vaccine_types WHERE id = vaccines.vaccine_type_id)');

        // 3. Drop all vaccine indexes before column manipulation
        $existing = collect(DB::select("PRAGMA index_list('vaccines')"))->pluck('name');
        $toDrop = [
            'vaccines_vaccine_type_id_index',
            'vaccines_type_rfid_date_index',
            'vaccines_date_id_index',
            'vaccines_user_id_index',
            'vaccines_workstation_id_index',
            'vaccines_rfid_tag_index',
        ];
        foreach ($toDrop as $idx) {
            if ($existing->contains($idx)) {
                Schema::table('vaccines', fn(Blueprint $t) => $t->dropIndex($idx));
            }
        }

        // 4. Drop vaccine_type_id column
        Schema::table('vaccines', function (Blueprint $table) {
            $table->dropColumn('vaccine_type_id');
        });

        // 5. Restore original performance indexes
        Schema::table('vaccines', function (Blueprint $table) {
            $table->index(['vaccine_type', 'rfid_tag', 'vaccination_date'], 'vaccines_type_rfid_date_index');
            $table->index(['vaccination_date', 'id'], 'vaccines_date_id_index');
            $table->index('user_id', 'vaccines_user_id_index');
            $table->index('workstation_id', 'vaccines_workstation_id_index');
            $table->index('rfid_tag', 'vaccines_rfid_tag_index');
        });

        Schema::dropIfExists('vaccine_types');
    }
};
