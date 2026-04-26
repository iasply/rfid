<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('vaccines', function (Blueprint $table) {
            // AlertController: MAX(vaccination_date) GROUP BY rfid_tag WHERE vaccine_type = ?
            $table->index(['vaccine_type', 'rfid_tag', 'vaccination_date'], 'vaccines_type_rfid_date_index');
            // VaccineController: ORDER BY vaccination_date DESC, id DESC (pagination)
            $table->index(['vaccination_date', 'id'], 'vaccines_date_id_index');
            // FK columns — SQLite does not auto-create indexes for foreign keys
            $table->index('user_id', 'vaccines_user_id_index');
            $table->index('workstation_id', 'vaccines_workstation_id_index');
            // AlertController whereIn rfid_tag; dashboard coverage subquery
            $table->index('rfid_tag', 'vaccines_rfid_tag_index');
        });

        Schema::table('cattle', function (Blueprint $table) {
            // FK column — no auto index in SQLite
            $table->index('user_id', 'cattle_user_id_index');
            // CattleController: ORDER BY created_at DESC (pagination)
            $table->index('created_at', 'cattle_created_at_index');
        });

        Schema::table('users', function (Blueprint $table) {
            // VeterinarianController: WHERE is_veterinarian = 1 ORDER BY created_at DESC
            $table->index(['is_veterinarian', 'created_at'], 'users_vet_created_at_index');
        });

        Schema::table('workstations', function (Blueprint $table) {
            // WorkstationController: ORDER BY created_at DESC (pagination)
            $table->index('created_at', 'workstations_created_at_index');
        });
    }

    public function down(): void
    {
        Schema::table('vaccines', function (Blueprint $table) {
            $table->dropIndex('vaccines_type_rfid_date_index');
            $table->dropIndex('vaccines_date_id_index');
            $table->dropIndex('vaccines_user_id_index');
            $table->dropIndex('vaccines_workstation_id_index');
            $table->dropIndex('vaccines_rfid_tag_index');
        });

        Schema::table('cattle', function (Blueprint $table) {
            $table->dropIndex('cattle_user_id_index');
            $table->dropIndex('cattle_created_at_index');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('users_vet_created_at_index');
        });

        Schema::table('workstations', function (Blueprint $table) {
            $table->dropIndex('workstations_created_at_index');
        });
    }
};
