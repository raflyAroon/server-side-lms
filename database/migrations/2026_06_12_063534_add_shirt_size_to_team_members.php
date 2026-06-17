// database/migrations/2026_06_12_000002_add_shirt_size_to_team_members.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('team_members', function (Blueprint $table) {
            $table->string('shirt_size', 10)->nullable()->after('study_program');
            // Optional: tambah index jika perlu
        });
    }

    public function down(): void
    {
        Schema::table('team_members', function (Blueprint $table) {
            $table->dropColumn('shirt_size');
        });
    }
};