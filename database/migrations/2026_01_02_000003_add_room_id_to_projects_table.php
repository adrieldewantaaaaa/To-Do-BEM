<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            // Nullable: existing personal projects keep room_id = null and work as before.
            $table->foreignId('room_id')->nullable()->after('user_id')->constrained()->cascadeOnDelete();
            $table->index('room_id');
        });
    }

    public function down(): void
    {
        Schema::table('projects', function (Blueprint $table) {
            $table->dropConstrainedForeignId('room_id');
        });
    }
};
