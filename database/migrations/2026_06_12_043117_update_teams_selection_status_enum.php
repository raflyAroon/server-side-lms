// database/migrations/2026_06_12_000001_update_teams_selection_status_enum.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Hapus constraint lama
        DB::statement('ALTER TABLE teams DROP CONSTRAINT teams_selection_status_check');

        // Buat constraint baru dengan enum yang diperluas
        DB::statement("ALTER TABLE teams ADD CONSTRAINT teams_selection_status_check CHECK (selection_status IN ('pending', 'lolos_seleksi', 'follow_the_bootcamp', 'first_half_hackathon', 'semi_final', 'final', 'rejected'))");
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE teams DROP CONSTRAINT teams_selection_status_check');
        DB::statement("ALTER TABLE teams ADD CONSTRAINT teams_selection_status_check CHECK (selection_status IN ('pending', 'approved', 'rejected'))");
    }
};