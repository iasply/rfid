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
        Schema::table('cattle', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable()->after('id');
            $table->foreign('user_id')->references('id')->on('users');
        });

        Schema::table('vaccines', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable()->after('vaccination_date');
            $table->foreign('user_id')->references('id')->on('users');
        });

        // Optionally, we could migrate existing data here if needed, 
        // but given the current state of the project, we'll just drop the old column.
        Schema::table('vaccines', function (Blueprint $table) {
            $table->dropColumn('vaccinator_username');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('vaccines', function (Blueprint $table) {
            $table->string('vaccinator_username')->nullable()->after('vaccination_date');
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });

        Schema::table('cattle', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });
    }
};
