<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // invite_code formatnya XXXX-XXXX-XXXX (14 char), kolom lama cuma 12.
        // Lebarkan jadi 16 di MySQL. SQLite tidak menegakkan panjang, jadi dilewati.
        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE rooms MODIFY invite_code VARCHAR(16) NOT NULL');
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE rooms MODIFY invite_code VARCHAR(12) NOT NULL');
        }
    }
};
