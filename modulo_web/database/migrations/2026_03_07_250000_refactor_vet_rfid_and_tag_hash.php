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
        Schema::table('users', function (Blueprint $table) {
            $table->renameColumn('username', 'vet_rfid');
        });

        // Ensure all users have a vet_rfid and tag_hash before enforcing NOT NULL
        $users = \Illuminate\Support\Facades\DB::table('users')->get();
        foreach ($users as $user) {
            $rfid = $user->vet_rfid;

            if (empty($rfid)) {
                $rfid = 'USER-' . $user->id;
                \Illuminate\Support\Facades\DB::table('users')
                    ->where('id', $user->id)
                    ->update(['vet_rfid' => $rfid]);
            }

            $hash = hash('sha256', $rfid . config('app.tag_salt'));
            \Illuminate\Support\Facades\DB::table('users')
                ->where('id', $user->id)
                ->update(['tag_hash' => $hash]);
        }

        Schema::table('users', function (Blueprint $table) {
            $table->string('vet_rfid')->nullable(false)->change();
            $table->string('tag_hash')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('tag_hash')->nullable(true)->change();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->renameColumn('vet_rfid', 'username');
        });
    }
};
