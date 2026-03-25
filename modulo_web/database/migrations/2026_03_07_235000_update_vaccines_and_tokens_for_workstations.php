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
        Schema::table('personal_access_tokens', function (Blueprint $table) {
            $table->unsignedBigInteger('workstation_id')->nullable()->after('tokenable_id');
            $table->foreign('workstation_id')->references('id')->on('workstations')->onDelete('cascade');
        });

        Schema::table('vaccines', function (Blueprint $table) {
            $table->unsignedBigInteger('workstation_id')->nullable()->after('vaccinator_username');
            $table->foreign('workstation_id')->references('id')->on('workstations');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vaccines', function (Blueprint $table) {
            $table->dropForeign(['workstation_id']);
            $table->dropColumn('workstation_id');
        });

        Schema::table('personal_access_tokens', function (Blueprint $table) {
            $table->dropForeign(['workstation_id']);
            $table->dropColumn('workstation_id');
        });
    }
};
