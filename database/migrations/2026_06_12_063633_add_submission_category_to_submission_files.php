<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('submission_files', function (Blueprint $table) {
            $table->string('submission_category', 50)->default('final_submission')->after('file_type');
            $table->index('submission_category');
        });
        // Tambah constraint check (opsional, lebih baik di kode)
        DB::statement("ALTER TABLE submission_files ADD CONSTRAINT submission_files_category_check CHECK (submission_category IN ('logbook_1', 'logbook_2', 'final_submission'))");
    }

    public function down(): void
    {
        Schema::table('submission_files', function (Blueprint $table) {
            $table->dropColumn('submission_category');
        });
        DB::statement('ALTER TABLE submission_files DROP CONSTRAINT submission_files_category_check');
    }
};