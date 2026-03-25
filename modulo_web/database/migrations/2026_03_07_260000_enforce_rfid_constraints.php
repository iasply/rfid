<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // SQLite doesn't support changing column length directly via 'change()' for strings easily in all versions,
        // but for Laravel 10+ it should work if doctrine/dbal is installed or using the new MariaDB/SQLite drivers.
        // However, since we want to be safe and enforce this, we apply it.

        Schema::table('cattle', function (Blueprint $table) {
            $table->string('rfid_tag', 16)->change();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('vet_rfid', 16)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cattle', function (Blueprint $table) {
            $table->string('rfid_tag')->unique()->change();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('vet_rfid')->unique()->change();
        });
    }
};
