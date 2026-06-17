<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Hapus constraint lama jika ada
        DB::statement('ALTER TABLE submissions DROP CONSTRAINT IF EXISTS submissions_project_type_check');

        // 2. Ubah data lama ke nilai baru yang sesuai
        //    website_application -> AI Application
        //    game_development   -> Game Dev
        //    video_design       -> Video Animation
        DB::statement("UPDATE submissions SET project_type = 'AI Application' WHERE project_type = 'website_application'");
        DB::statement("UPDATE submissions SET project_type = 'Game Dev' WHERE project_type = 'game_development'");
        DB::statement("UPDATE submissions SET project_type = 'Video Animation' WHERE project_type = 'video_design'");

        // 3. Tambahkan constraint baru dengan nilai yang diperbolehkan
        DB::statement("ALTER TABLE submissions ADD CONSTRAINT submissions_project_type_check CHECK (project_type IN ('AI Application', 'Game Dev', 'Video Animation'))");
    }

    public function down(): void
    {
        // Hapus constraint baru
        DB::statement('ALTER TABLE submissions DROP CONSTRAINT IF EXISTS submissions_project_type_check');
        // Kembalikan data ke nilai lama (opsional, tetapi disarankan)
        DB::statement("UPDATE submissions SET project_type = 'website_application' WHERE project_type = 'AI Application'");
        DB::statement("UPDATE submissions SET project_type = 'game_development' WHERE project_type = 'Game Dev'");
        DB::statement("UPDATE submissions SET project_type = 'video_design' WHERE project_type = 'Video Animation'");
        // Kembalikan constraint lama (jika diperlukan)
        DB::statement("ALTER TABLE submissions ADD CONSTRAINT submissions_project_type_check CHECK (project_type IN ('website_application', 'game_development', 'video_design'))");
    }
};