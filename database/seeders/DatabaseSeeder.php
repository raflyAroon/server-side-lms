<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Users
        $users = [
            ['name' => 'Admin Utama', 'email' => 'admin@codingmu.com', 'password_hash' => Hash::make('admin123'), 'role' => 'admin'],
            ['name' => 'Juri Senior', 'email' => 'juri1@codingmu.com', 'password_hash' => Hash::make('juri123'), 'role' => 'juri'],
            ['name' => 'Juri Teknik', 'email' => 'juri2@codingmu.com', 'password_hash' => Hash::make('juri123'), 'role' => 'juri'],
            ['name' => 'Budi Santoso', 'email' => 'budi@timkreatif.id', 'password_hash' => Hash::make('peserta123'), 'role' => 'peserta'],
            ['name' => 'Siti Nurhaliza', 'email' => 'siti@devteam.id', 'password_hash' => Hash::make('peserta123'), 'role' => 'peserta'],
            ['name' => 'Agus Wijaya', 'email' => 'agus@gamezone.id', 'password_hash' => Hash::make('peserta123'), 'role' => 'peserta'],
        ];
        DB::table('users')->insert($users);

        // 2. Teams
        $teams = [
            ['team_name' => 'Tim Kreatif', 'institution' => 'Universitas Indonesia', 'ketua_id' => 4],
            ['team_name' => 'Dev Team', 'institution' => 'ITB', 'ketua_id' => 5],
            ['team_name' => 'Game Zone', 'institution' => 'BINUS', 'ketua_id' => 6],
        ];
        DB::table('teams')->insert($teams);

        // 3. Team Histories
        DB::table('team_histories')->insert([
            ['team_id' => 1, 'snapshot_data' => json_encode(['team_name'=>'Tim Kreatif','institution'=>'Universitas Indonesia','ketua_id'=>4]), 'changed_by' => 'budi@timkreatif.id', 'changed_at' => now()],
            ['team_id' => 1, 'snapshot_data' => json_encode(['team_name'=>'Tim Kreatif Updated','institution'=>'Universitas Indonesia Depok','ketua_id'=>4]), 'changed_by' => 'budi@timkreatif.id', 'changed_at' => now()->subDays(2)],
        ]);

        // 4. Event
        DB::table('events')->insert([
            'name' => 'Lomba Coding MPR RI 2026',
            'description' => 'Kompetisi nasional pengembangan solusi digital untuk MPR RI',
            'start_date' => '2026-06-01',
            'end_date' => '2026-08-30',
        ]);

        // 5. Stages (sesuai swimlane)
        $stages = [
            ['event_id' => 1, 'name' => 'Seleksi Administrasi', 'stage_order' => 1, 'is_active' => true],
            ['event_id' => 1, 'name' => 'Seleksi Karya', 'stage_order' => 2, 'is_active' => true],
            ['event_id' => 1, 'name' => 'Bootcamp', 'stage_order' => 3, 'is_active' => true],
            ['event_id' => 1, 'name' => 'Hackathon 1', 'stage_order' => 4, 'is_active' => true],
            ['event_id' => 1, 'name' => 'Hackathon 2', 'stage_order' => 5, 'is_active' => true],
            ['event_id' => 1, 'name' => 'Semifinal', 'stage_order' => 6, 'is_active' => true],
            ['event_id' => 1, 'name' => 'Final', 'stage_order' => 7, 'is_active' => true],
        ];
        DB::table('stages')->insert($stages);

        // 6. Submissions
        DB::table('submissions')->insert([
            ['team_id' => 1, 'stage_id' => 1, 'project_type' => 'website_application', 'description' => 'Platform edukasi interaktif untuk MPR'],
            ['team_id' => 2, 'stage_id' => 1, 'project_type' => 'game_development', 'description' => 'Game simulasi tata cara legislasi'],
            ['team_id' => 3, 'stage_id' => 1, 'project_type' => 'video_design', 'description' => 'Video animasi sejarah MPR'],
        ]);

        // 7. Submission Files (upload massal contoh)
        DB::table('submission_files')->insert([
            ['submission_id' => 1, 'file_url' => '/uploads/team1/proposal.pdf', 'file_name' => 'Proposal_TimKreatif.pdf', 'file_size' => 2048000],
            ['submission_id' => 1, 'file_url' => '/uploads/team1/poster.jpg', 'file_name' => 'Poster_TimKreatif.jpg', 'file_size' => 512000],
            ['submission_id' => 1, 'file_url' => '/uploads/team1/source.zip', 'file_name' => 'SourceCode_TimKreatif.zip', 'file_size' => 10240000],
            ['submission_id' => 2, 'file_url' => '/uploads/team2/game.apk', 'file_name' => 'GameDevTeam.apk', 'file_size' => 15728640],
            ['submission_id' => 2, 'file_url' => '/uploads/team2/dokumentasi.pdf', 'file_name' => 'Dokumentasi_DevTeam.pdf', 'file_size' => 1024000],
            ['submission_id' => 3, 'file_url' => '/uploads/team3/video.mp4', 'file_name' => 'Animasi_MPR.mp4', 'file_size' => 52428800],
        ]);

        // 8. Rubrics & Rubric Criteria (dinamis)
        DB::table('rubrics')->insert([
            ['stage_id' => 2, 'name' => 'Rubrik Penilaian Karya Tahap Seleksi', 'description' => 'Kriteria untuk seleksi karya'],
            ['stage_id' => 4, 'name' => 'Rubrik Hackathon 1', 'description' => 'Penilaian untuk hackathon putaran pertama'],
        ]);

        DB::table('rubric_criteria')->insert([
            ['rubric_id' => 1, 'criterion_name' => 'Kreativitas', 'max_score' => 100, 'weight' => 0.3],
            ['rubric_id' => 1, 'criterion_name' => 'Kesesuaian Tema', 'max_score' => 100, 'weight' => 0.25],
            ['rubric_id' => 1, 'criterion_name' => 'Teknis Implementasi', 'max_score' => 100, 'weight' => 0.25],
            ['rubric_id' => 1, 'criterion_name' => 'Dokumentasi', 'max_score' => 100, 'weight' => 0.2],
            ['rubric_id' => 2, 'criterion_name' => 'Inovasi', 'max_score' => 100, 'weight' => 0.35],
            ['rubric_id' => 2, 'criterion_name' => 'User Experience', 'max_score' => 100, 'weight' => 0.35],
            ['rubric_id' => 2, 'criterion_name' => 'Presentasi', 'max_score' => 100, 'weight' => 0.3],
        ]);

        // 9. Scores & Score Details (rekap otomatis)
        DB::table('scores')->insert([
            ['submission_id' => 1, 'juri_id' => 2, 'total_score' => 85.5, 'feedback' => 'Kreatif, namun dokumentasi kurang lengkap'],
            ['submission_id' => 2, 'juri_id' => 2, 'total_score' => 90.0, 'feedback' => 'Game menarik, sangat sesuai tema'],
            ['submission_id' => 1, 'juri_id' => 3, 'total_score' => 88.0, 'feedback' => 'Bagus, perlu peningkatan UX'],
        ]);

        DB::table('score_details')->insert([
            ['score_id' => 1, 'rubric_criteria_id' => 1, 'score_value' => 90],
            ['score_id' => 1, 'rubric_criteria_id' => 2, 'score_value' => 85],
            ['score_id' => 1, 'rubric_criteria_id' => 3, 'score_value' => 80],
            ['score_id' => 1, 'rubric_criteria_id' => 4, 'score_value' => 87],
            ['score_id' => 2, 'rubric_criteria_id' => 1, 'score_value' => 95],
            ['score_id' => 2, 'rubric_criteria_id' => 2, 'score_value' => 90],
            ['score_id' => 2, 'rubric_criteria_id' => 3, 'score_value' => 85],
            ['score_id' => 2, 'rubric_criteria_id' => 4, 'score_value' => 90],
            ['score_id' => 3, 'rubric_criteria_id' => 1, 'score_value' => 88],
            ['score_id' => 3, 'rubric_criteria_id' => 2, 'score_value' => 90],
            ['score_id' => 3, 'rubric_criteria_id' => 3, 'score_value' => 85],
            ['score_id' => 3, 'rubric_criteria_id' => 4, 'score_value' => 89],
        ]);

        // 10. Selection Results
        DB::table('selection_results')->insert([
            ['team_id' => 1, 'stage_id' => 1, 'is_passed' => true, 'note' => 'Lolos seleksi administrasi', 'announced_at' => now()],
            ['team_id' => 2, 'stage_id' => 1, 'is_passed' => true, 'note' => 'Lolos', 'announced_at' => now()],
            ['team_id' => 3, 'stage_id' => 1, 'is_passed' => false, 'note' => 'Tidak memenuhi syarat administrasi', 'announced_at' => now()],
            ['team_id' => 1, 'stage_id' => 2, 'is_passed' => true, 'note' => 'Karya bagus, lolos ke bootcamp', 'announced_at' => now()],
            ['team_id' => 2, 'stage_id' => 2, 'is_passed' => true, 'note' => 'Lolos ke bootcamp', 'announced_at' => now()],
        ]);

        // 11. Schedules
        DB::table('schedules')->insert([
            ['event_id' => 1, 'date_time' => '2026-06-01 08:00:00', 'description' => 'Pembukaan Lomba', 'location' => 'Online - Zoom'],
            ['event_id' => 1, 'date_time' => '2026-06-10 23:59:00', 'description' => 'Batas Submit Seleksi Karya', 'location' => 'Online LMS'],
            ['event_id' => 1, 'date_time' => '2026-07-01 09:00:00', 'description' => 'Pengumuman Hasil Seleksi', 'location' => 'Online'],
            ['event_id' => 1, 'date_time' => '2026-07-05 08:00:00', 'description' => 'Bootcamp Hari 1', 'location' => 'Gedung MPR/Online Hybrid'],
            ['event_id' => 1, 'date_time' => '2026-07-20 08:00:00', 'description' => 'Hackathon 1', 'location' => 'Online'],
            ['event_id' => 1, 'date_time' => '2026-08-25 14:00:00', 'description' => 'Grand Final & Pengumuman Juara', 'location' => 'Gedung MPR'],
        ]);

        // 12. Announcements (personalisasi)
        DB::table('announcements')->insert([
            ['title' => 'Pengumuman Hasil Seleksi Administrasi', 'content' => 'Selamat! Tim Anda lolos ke tahap seleksi karya.', 'target_team_id' => 1, 'target_stage_id' => 1, 'type' => 'team', 'published_at' => now()],
            ['title' => 'Pengumuman Hasil Seleksi Administrasi', 'content' => 'Mohon maaf, tim Anda belum berhasil melaju.', 'target_team_id' => 3, 'target_stage_id' => 1, 'type' => 'team', 'published_at' => now()],
            ['title' => 'Jadwal Bootcamp', 'content' => 'Bootcamp akan dilaksanakan pada 5-6 Juli 2026 secara hybrid.', 'target_team_id' => null, 'target_stage_id' => 3, 'type' => 'stage', 'published_at' => now()],
            ['title' => 'Info Lomba', 'content' => 'Pendaftaran ditutup 31 Mei 2026.', 'target_team_id' => null, 'target_stage_id' => null, 'type' => 'global', 'published_at' => now()],
        ]);

        // 13. Certificates
        DB::table('certificates')->insert([
            ['team_id' => 1, 'event_id' => 1, 'certificate_url' => '/certificates/Team1_Peserta.pdf'],
            ['team_id' => 2, 'event_id' => 1, 'certificate_url' => '/certificates/Team2_Peserta.pdf'],
        ]);

        // 14. FAQs
        DB::table('faqs')->insert([
            ['question' => 'Apakah bisa mengganti anggota tim?', 'answer' => 'Hanya ketua tim yang dapat mengupdate data tim melalui menu Profil Tim.', 'display_order' => 1],
            ['question' => 'Berapa maksimal file upload?', 'answer' => 'Maksimal 10 file per submission, masing-masing 50MB.', 'display_order' => 2],
            ['question' => 'Bagaimana sistem penilaian?', 'answer' => 'Juri menilai berdasarkan rubrik yang sudah ditentukan. Nilai akhir dihitung otomatis.', 'display_order' => 3],
        ]);

        // 15. OTPs (contoh)
        DB::table('otps')->insert([
            ['user_id' => 4, 'code' => '123456', 'expires_at' => Carbon::now()->addMinutes(5), 'is_used' => false],
            ['user_id' => 5, 'code' => '654321', 'expires_at' => Carbon::now()->addMinutes(5), 'is_used' => true],
        ]);

        // 16. Audit Logs
        DB::table('audit_logs')->insert([
            ['user_id' => 1, 'action' => 'UPDATE', 'entity_type' => 'team', 'entity_id' => 1, 'old_value_json' => json_encode(['institution'=>'Universitas Indonesia']), 'new_value_json' => json_encode(['institution'=>'Universitas Indonesia Depok']), 'ip_address' => '192.168.1.10'],
            ['user_id' => 2, 'action' => 'CREATE', 'entity_type' => 'score', 'entity_id' => 1, 'old_value_json' => null, 'new_value_json' => json_encode(['total_score'=>85.5,'juri_id'=>2]), 'ip_address' => '10.0.0.5'],
            ['user_id' => 4, 'action' => 'UPDATE', 'entity_type' => 'submission', 'entity_id' => 1, 'old_value_json' => json_encode(['description'=>'Platform edukasi']), 'new_value_json' => json_encode(['description'=>'Platform edukasi interaktif']), 'ip_address' => '203.0.113.5'],
        ]);
    }
}