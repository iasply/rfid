<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        \DB::statement("
            CREATE VIEW cattle_with_vaccines_view AS
            SELECT
                c.id,
                c.rfid_tag,
                c.name,
                c.weight,
                c.registration_date,
                c.user_id,
                COUNT(v.id) AS vaccines_count
            FROM cattle c
            LEFT JOIN vaccines v ON c.rfid_tag = v.rfid_tag
            GROUP BY c.id, c.rfid_tag, c.name, c.weight, c.registration_date, c.user_id
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        \DB::statement("DROP VIEW IF EXISTS cattle_with_vaccines_view");
    }
};
