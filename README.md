# 📘 README.md (Full Template)

# LMS Backend - CodingMu & MPR RI
Backend REST API untuk sistem manajemen lomba coding, bootcamp, dan hackathon MPR RI. Dibangun dengan Laravel 12, PostgreSQL, Redis, Sanctum (cookie authentication), Queue (Redis), Job & Event, serta Observer untuk audit log dan rekap nilai otomatis.

## 📋 Daftar Isi
- [Teknologi](#-teknologi)
- [Fitur Utama](#-fitur-utama)
- [Persyaratan Sistem](#-persyaratan-sistem)
- [Instalasi](#-instalasi)
  - [1. Clone Repository](#1-clone-repository)
  - [2. Install Dependencies](#2-install-dependencies)
  - [3. Konfigurasi Environment](#3-konfigurasi-environment)
  - [4. Generate Key](#4-generate-key)
  - [5. Database & Migrasi](#5-database--migrasi)
  - [6. Redis & Queue](#6-redis--queue)
  - [7. Storage Link](#7-storage-link)
  - [8. Jalankan Server](#8-jalankan-server)
- [Testing](#-testing)
  - [Postman Collection](#postman-collection)
  - [PHPUnit](#phpunit)
- [Deployment ke GitHub](#-deployment-ke-github)
- [Struktur Folder Penting](#-struktur-folder-penting)
- [API Endpoints](#-api-endpoints)
- [Lisensi](#-lisensi)

## 🧰 Teknologi
| Komponen       | Versi / Teknologi                          |
|----------------|--------------------------------------------|
| PHP            | 8.2+                                       |
| Laravel        | 12.x                                       |
| Database       | PostgreSQL 14+                             |
| Queue / Cache  | Redis + Predis                             |
| Auth           | Laravel Sanctum (cookie-based)             |
| Export         | maatwebsite/excel 3.1+                     |
| PDF            | barryvdh/laravel-dompdf                    |
| SMTP           | Gmail / SMTP lain (OTP via email)          |

## ✨ Fitur Utama
- ✅ Multi-role (Admin, Juri, Peserta) dengan middleware.
- ✅ Register & Login via OTP 6 digit (email SMTP).
- ✅ Manajemen Tim (hanya ketua tim) + riwayat perubahan + restore data.
- ✅ Submission karya dengan upload massal (max 10 file).
- ✅ Rubrik penilaian dinamis per stage + rekap nilai otomatis.
- ✅ Personalisasi pengumuman (global, per stage, per tim).
- ✅ Dashboard berbeda untuk setiap role.
- ✅ Export data (Teams, Scores, Selection Results, Submissions) ke Excel.
- ✅ Sertifikat otomatis (PDF via queue).
- ✅ Audit log semua aktivitas + restore data dari history.
- ✅ FAQ dinamis (CRUD admin).
- ✅ Cache Redis untuk query berat (dashboard, rubrik, pengumuman, dll).
- ✅ Queue & Job (OTP email, export, generate sertifikat).

## 📦 Persyaratan Sistem
- PHP 8.2+ dengan ekstensi: `pdo_pgsql`, `zip`, `gd`, `redis`, `fileinfo`, `mbstring`, `exif`.
- PostgreSQL 14+
- Redis Server (untuk queue & cache)
- Composer
- Node.js & NPM (hanya jika diperlukan frontend, tapi backend tidak wajib)

## 🚀 Instalasi
### 1. Clone Repository

```bash
git clone https://github.com/username/lms-backend.git
cd lms-backend
```

### 2. Install Dependencies

```bash
composer install
```

Jika belum punya, install juga Redis PHP extension:

- Windows: download `php_redis.dll` dari PECL dan aktifkan di `php.ini`.
- Linux: `sudo apt install php8.2-redis`

### 3. Konfigurasi Environment

Salin `.env.example` menjadi `.env`:

```bash
cp .env.example .env
```

Edit `.env` sesuai kebutuhan. Contoh minimal:

# code untuk .env
```
APP_NAME="LMS CodingMu MPR"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000
FRONTEND_URL=http://localhost:3000

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=lms_codingmu
DB_USERNAME=postgres
DB_PASSWORD=yourpassword

QUEUE_CONNECTION=redis
CACHE_DRIVER=redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your@gmail.com
MAIL_PASSWORD=app_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=no-reply@codingmu.com
MAIL_FROM_NAME="LMS CodingMu"

SANCTUM_STATEFUL_DOMAINS=localhost:3000
SESSION_DOMAIN=localhost
```
### 4. Generate Key

```bash
php artisan key:generate
```

### 5. Database & Migrasi

Buat database PostgreSQL (misal `lms-codingmu-mpr`), lalu:

```bash
php artisan migrate
php artisan db:seed
```

Seeder akan membuat:
- 1 event, 7 stage, 3 tim, 3 user (admin, juri, peserta).
- Data dummy untuk submission, rubrik, nilai, pengumuman, dll.

### 6. Redis & Queue
Pastikan Redis server berjalan:

```bash
redis-server
```

Jalankan queue worker:

```bash
php artisan queue:work redis --tries=3
```

> Untuk development, bisa sementara ubah `QUEUE_CONNECTION=sync` di .env.

### 7. Storage Link

```bash
php artisan storage:link
```

### 8. Jalankan Server

```bash
php artisan serve
```

Backend berjalan di `http://localhost:8000`.

---

## 🧪 Testing

### Postman Collection

1. **Download Postman** (https://www.postman.com/).
2. **Import** file koleksi `LMS_Backend.postman_collection.json` (saya sediakan di bagian akhir README).
3. **Environment** buat dengan variabel:
   - `base_url` = `http://localhost:8000/api`
   - `user_id` (isi setelah register/verify OTP)
   - `submission_id` (setelah create submission)
   - `team_id` (dari seeder: 1,2,3)
4. **Testing flow** (minimal):
   - Register user → verify OTP → GET /me → GET /team → PUT /team → POST /submissions → upload files → Login sebagai juri → POST /scores → GET /scores/auto-recap → Login admin → export teams → GET /dashboard/admin → dll.

### PHPUnit

Jalankan unit tests:

```bash
php artisan test
```

Atau test spesifik:

```bash
php artisan test --filter=TeamControllerTest
```

---

## ☁️ Deployment ke GitHub

### 1. Inisialisasi Git

```bash
git init
git add .
git commit -m "Initial commit: LMS backend Laravel 12"
```

### 2. Buat repository di GitHub (tanpa README, .gitignore).

### 3. Push ke remote

```bash
git remote add origin https://github.com/username/lms-backend.git
git branch -M main
git push -u origin main
```

### 4. Siapkan .gitignore (sudah default laravel, tambahkan):

```
.env
.env.backup
.phpunit.result.cache
/storage/logs/*.log
/public/storage
```

### 5. Tagging (opsional)

```bash
git tag -a v1.0.0 -m "Release pertama"
git push origin v1.0.0
```

---

## 📁 Struktur Folder Penting

```
backend/
├── app/
│   ├── Console/Commands/          # artisan custom
│   ├── Events/                    # ModelUpdated dll
│   ├── Exports/                   # Excel exports
│   ├── Http/
│   │   ├── Controllers/Api/       # Auth, Team, Submission, Score, dll
│   │   ├── Middleware/            # RoleMiddleware
│   │   └── Requests/Api/          # FormRequest validasi
│   ├── Jobs/                      # SendOtpEmail, ExportData, GenerateCertificate
│   ├── Listeners/                 # LogModelChanges (Event listener)
│   ├── Models/                    # Semua model Eloquent
│   ├── Observers/                 # TeamObserver, SubmissionObserver, ScoreObserver
│   ├── Providers/                 # EventServiceProvider (daftar observer)
│   ├── Services/                  # CacheService
│   └── Traits/                    # Cacheable
├── bootstrap/
│   └── providers.php              # Daftar provider (termasuk EventServiceProvider)
├── config/
│   ├── cors.php                   # CORS untuk Next.js
│   ├── sanctum.php                # Stateful domains & cookie
│   └── queue.php                  # Driver redis
├── database/
│   ├── migrations/                # Semua migration
│   └── seeders/                   # DatabaseSeeder
├── resources/views/
│   ├── emails/otp.blade.php
│   └── pdf/certificate.blade.php
├── routes/
│   └── api.php                    # Semua endpoint REST
└── .env                           # Konfigurasi lokal
```

---

## Query Database for PostgreSQL
-- =====================================================
-- DATABASE: LMS_CODINGMU_MPR
-- PostgreSQL 14+
-- =====================================================

-- Hapus tabel jika ada (urutan FK terbalik)
DROP TABLE IF EXISTS score_detail CASCADE;
DROP TABLE IF EXISTS score CASCADE;
DROP TABLE IF EXISTS submission_file CASCADE;
DROP TABLE IF EXISTS submission CASCADE;
DROP TABLE IF EXISTS selection_result CASCADE;
DROP TABLE IF EXISTS certificate CASCADE;
DROP TABLE IF EXISTS announcement CASCADE;
DROP TABLE IF EXISTS audit_log CASCADE;
DROP TABLE IF EXISTS otp CASCADE;
DROP TABLE IF EXISTS rubric_criteria CASCADE;
DROP TABLE IF EXISTS rubric CASCADE;
DROP TABLE IF EXISTS schedule CASCADE;
DROP TABLE IF EXISTS stage CASCADE;
DROP TABLE IF EXISTS event CASCADE;
DROP TABLE IF EXISTS team_history CASCADE;
DROP TABLE IF EXISTS team CASCADE;
DROP TABLE IF EXISTS faq CASCADE;
DROP TABLE IF EXISTS users CASCADE;

-- Hapus enum types jika ada
DROP TYPE IF EXISTS user_role CASCADE;
DROP TYPE IF EXISTS project_type_enum CASCADE;
DROP TYPE IF EXISTS announcement_type CASCADE;

-- =====================================================
-- ENUM TYPES
-- =====================================================
CREATE TYPE user_role AS ENUM ('admin', 'juri', 'peserta');
CREATE TYPE project_type_enum AS ENUM ('website_application', 'game_development', 'video_design');
CREATE TYPE announcement_type AS ENUM ('global', 'stage', 'team');

-- =====================================================
-- TABLES
-- =====================================================

-- 1. USERS
CREATE TABLE users (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    role user_role NOT NULL DEFAULT 'peserta',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 2. TEAM (hanya ketua tim sebagai user)
CREATE TABLE team (
    id SERIAL PRIMARY KEY,
    team_name VARCHAR(100) NOT NULL,
    institution VARCHAR(200),
    ketua_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 3. TEAM_HISTORY (riwayat perubahan data tim)
CREATE TABLE team_history (
    id SERIAL PRIMARY KEY,
    team_id INTEGER NOT NULL REFERENCES team(id) ON DELETE CASCADE,
    snapshot_data JSONB NOT NULL,  -- menyimpan full snapshot data tim sebelum perubahan
    changed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    changed_by VARCHAR(100)  -- bisa diisi email atau user_id
);

-- 4. EVENT
CREATE TABLE event (
    id SERIAL PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    description TEXT,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 5. STAGE
CREATE TABLE stage (
    id SERIAL PRIMARY KEY,
    event_id INTEGER NOT NULL REFERENCES event(id) ON DELETE CASCADE,
    name VARCHAR(100) NOT NULL,
    stage_order INTEGER NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 6. SUBMISSION
CREATE TABLE submission (
    id SERIAL PRIMARY KEY,
    team_id INTEGER NOT NULL REFERENCES team(id) ON DELETE CASCADE,
    stage_id INTEGER NOT NULL REFERENCES stage(id) ON DELETE CASCADE,
    project_type project_type_enum NOT NULL,
    description TEXT,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 7. SUBMISSION_FILE (upload massal)
CREATE TABLE submission_file (
    id SERIAL PRIMARY KEY,
    submission_id INTEGER NOT NULL REFERENCES submission(id) ON DELETE CASCADE,
    file_url TEXT NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_size INTEGER, -- in bytes
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 8. RUBRIC (dinamis per stage)
CREATE TABLE rubric (
    id SERIAL PRIMARY KEY,
    stage_id INTEGER NOT NULL REFERENCES stage(id) ON DELETE CASCADE,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 9. RUBRIC_CRITERIA
CREATE TABLE rubric_criteria (
    id SERIAL PRIMARY KEY,
    rubric_id INTEGER NOT NULL REFERENCES rubric(id) ON DELETE CASCADE,
    criterion_name VARCHAR(100) NOT NULL,
    max_score INTEGER NOT NULL CHECK (max_score > 0),
    weight DECIMAL(5,2) DEFAULT 1.0 CHECK (weight >= 0),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 10. SCORE (rekap nilai otomatis)
CREATE TABLE score (
    id SERIAL PRIMARY KEY,
    submission_id INTEGER NOT NULL REFERENCES submission(id) ON DELETE CASCADE,
    juri_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    total_score DECIMAL(10,2) NOT NULL,
    feedback TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 11. SCORE_DETAIL (nilai per kriteria)
CREATE TABLE score_detail (
    id SERIAL PRIMARY KEY,
    score_id INTEGER NOT NULL REFERENCES score(id) ON DELETE CASCADE,
    rubric_criteria_id INTEGER NOT NULL REFERENCES rubric_criteria(id) ON DELETE CASCADE,
    score_value DECIMAL(10,2) NOT NULL CHECK (score_value >= 0),
    UNIQUE(score_id, rubric_criteria_id)
);

-- 12. SELECTION_RESULT (hasil kelulusan tiap stage)
CREATE TABLE selection_result (
    id SERIAL PRIMARY KEY,
    team_id INTEGER NOT NULL REFERENCES team(id) ON DELETE CASCADE,
    stage_id INTEGER NOT NULL REFERENCES stage(id) ON DELETE CASCADE,
    is_passed BOOLEAN NOT NULL,
    note TEXT,
    announced_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(team_id, stage_id)
);

-- 13. SCHEDULE
CREATE TABLE schedule (
    id SERIAL PRIMARY KEY,
    event_id INTEGER NOT NULL REFERENCES event(id) ON DELETE CASCADE,
    date_time TIMESTAMP NOT NULL,
    description TEXT,
    location VARCHAR(200),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 14. ANNOUNCEMENT (personalisasi pengumuman)
CREATE TABLE announcement (
    id SERIAL PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    content TEXT NOT NULL,
    target_team_id INTEGER REFERENCES team(id) ON DELETE CASCADE,
    target_stage_id INTEGER REFERENCES stage(id) ON DELETE CASCADE,
    type announcement_type NOT NULL,
    published_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 15. CERTIFICATE (sertifikat otomatis)
CREATE TABLE certificate (
    id SERIAL PRIMARY KEY,
    team_id INTEGER NOT NULL REFERENCES team(id) ON DELETE CASCADE,
    event_id INTEGER NOT NULL REFERENCES event(id) ON DELETE CASCADE,
    certificate_url TEXT NOT NULL,
    generated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 16. FAQ (dinamis)
CREATE TABLE faq (
    id SERIAL PRIMARY KEY,
    question TEXT NOT NULL,
    answer TEXT NOT NULL,
    display_order INTEGER DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 17. OTP (kode via email tanpa Google API)
CREATE TABLE otp (
    id SERIAL PRIMARY KEY,
    user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    code VARCHAR(6) NOT NULL,
    expires_at TIMESTAMP NOT NULL,
    is_used BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 18. AUDIT_LOG (log aktivitas dan restore data)
CREATE TABLE audit_log (
    id SERIAL PRIMARY KEY,
    user_id INTEGER REFERENCES users(id) ON DELETE SET NULL,
    action VARCHAR(50) NOT NULL,  -- CREATE, UPDATE, DELETE
    entity_type VARCHAR(50) NOT NULL,
    entity_id INTEGER NOT NULL,
    old_value_json JSONB,
    new_value_json JSONB,
    ip_address INET,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- =====================================================
-- TRIGGER untuk updated_at (opsional)
-- =====================================================
CREATE OR REPLACE FUNCTION update_updated_at_column()
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = CURRENT_TIMESTAMP;
    RETURN NEW;
END;
$$ language 'plpgsql';

CREATE TRIGGER update_users_updated_at BEFORE UPDATE ON users FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();
CREATE TRIGGER update_team_updated_at BEFORE UPDATE ON team FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();
CREATE TRIGGER update_submission_updated_at BEFORE UPDATE ON submission FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();
CREATE TRIGGER update_score_updated_at BEFORE UPDATE ON score FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();
CREATE TRIGGER update_faq_updated_at BEFORE UPDATE ON faq FOR EACH ROW EXECUTE FUNCTION update_updated_at_column();

-- =====================================================
-- INDEX untuk performa query
-- =====================================================
CREATE INDEX idx_team_ketua ON team(ketua_id);
CREATE INDEX idx_submission_team ON submission(team_id);
CREATE INDEX idx_submission_stage ON submission(stage_id);
CREATE INDEX idx_score_submission ON score(submission_id);
CREATE INDEX idx_score_juri ON score(juri_id);
CREATE INDEX idx_selection_result_team_stage ON selection_result(team_id, stage_id);
CREATE INDEX idx_announcement_target_team ON announcement(target_team_id);
CREATE INDEX idx_announcement_target_stage ON announcement(target_stage_id);
CREATE INDEX idx_audit_log_entity ON audit_log(entity_type, entity_id);
CREATE INDEX idx_otp_user ON otp(user_id);
CREATE INDEX idx_otp_code_expires ON otp(code, expires_at);

-- =====================================================
-- DATA DUMMY
-- =====================================================

-- Insert Users
INSERT INTO users (name, email, password_hash, role) VALUES
('Admin Utama', 'admin@codingmu.com', 'hash_admin123', 'admin'),
('Juri Senior', 'juri1@codingmu.com', 'hash_juri', 'juri'),
('Juri Teknik', 'juri2@codingmu.com', 'hash_juri2', 'juri'),
('Budi Santoso', 'budi@timkreatif.id', 'hash_budi', 'peserta'),
('Siti Nurhaliza', 'siti@devteam.id', 'hash_siti', 'peserta'),
('Agus Wijaya', 'agus@gamezone.id', 'hash_agus', 'peserta');

-- Insert Team
INSERT INTO team (team_name, institution, ketua_id) VALUES
('Tim Kreatif', 'Universitas Indonesia', 4),
('Dev Team', 'ITB', 5),
('Game Zone', 'BINUS', 6);

-- Insert Team History (riwayat perubahan data tim)
INSERT INTO team_history (team_id, snapshot_data, changed_by) VALUES
(1, '{"team_name":"Tim Kreatif","institution":"Universitas Indonesia","ketua_id":4}', 'budi@timkreatif.id'),
(1, '{"team_name":"Tim Kreatif Updated","institution":"Universitas Indonesia Depok","ketua_id":4}', 'budi@timkreatif.id'),
(2, '{"team_name":"Dev Team","institution":"ITB Bandung","ketua_id":5}', 'siti@devteam.id');

-- Insert Event
INSERT INTO event (name, description, start_date, end_date) VALUES
('Lomba Coding MPR RI 2026', 'Kompetisi nasional pengembangan solusi digital untuk MPR RI', '2026-06-01', '2026-08-30');

-- Insert Stage (sesuai swimlane: seleksi -> bootcamp -> hackathon1 -> hackathon2 -> semifinal -> final)
INSERT INTO stage (event_id, name, stage_order, is_active) VALUES
(1, 'Seleksi Administrasi', 1, TRUE),
(1, 'Seleksi Karya', 2, TRUE),
(1, 'Bootcamp', 3, TRUE),
(1, 'Hackathon 1', 4, TRUE),
(1, 'Hackathon 2', 5, TRUE),
(1, 'Semifinal', 6, TRUE),
(1, 'Final', 7, TRUE);

-- Insert Schedule
INSERT INTO schedule (event_id, date_time, description, location) VALUES
(1, '2026-06-01 08:00:00', 'Pembukaan Lomba', 'Online - Zoom'),
(1, '2026-06-10 23:59:00', 'Batas Submit Seleksi Karya', 'Online LMS'),
(1, '2026-07-01 09:00:00', 'Pengumuman Hasil Seleksi', 'Online'),
(1, '2026-07-05 08:00:00', 'Bootcamp Hari 1', 'Gedung MPR/Online Hybrid'),
(1, '2026-07-20 08:00:00', 'Hackathon 1', 'Online'),
(1, '2026-08-25 14:00:00', 'Grand Final & Pengumuman Juara', 'Gedung MPR');

-- Insert Submission & Submission Files (upload massal)
INSERT INTO submission (team_id, stage_id, project_type, description) VALUES
(1, 1, 'website_application', 'Platform edukasi interaktif untuk MPR'),
(2, 1, 'game_development', 'Game simulasi tata cara legislasi'),
(3, 1, 'video_design', 'Video animasi sejarah MPR');

INSERT INTO submission_file (submission_id, file_url, file_name, file_size) VALUES
(1, '/uploads/team1/proposal.pdf', 'Proposal_TimKreatif.pdf', 2048000),
(1, '/uploads/team1/poster.jpg', 'Poster_TimKreatif.jpg', 512000),
(1, '/uploads/team1/source.zip', 'SourceCode_TimKreatif.zip', 10240000),
(2, '/uploads/team2/game.apk', 'GameDevTeam.apk', 15728640),
(2, '/uploads/team2/dokumentasi.pdf', 'Dokumentasi_DevTeam.pdf', 1024000),
(3, '/uploads/team3/video.mp4', 'Animasi_MPR.mp4', 52428800);

-- Insert Rubric (dinamis per stage)
INSERT INTO rubric (stage_id, name, description) VALUES
(2, 'Rubrik Penilaian Karya Tahap Seleksi', 'Kriteria untuk seleksi karya'),
(4, 'Rubrik Hackathon 1', 'Penilaian untuk hackathon putaran pertama');

INSERT INTO rubric_criteria (rubric_id, criterion_name, max_score, weight) VALUES
(1, 'Kreativitas', 100, 0.3),
(1, 'Kesesuaian Tema', 100, 0.25),
(1, 'Teknis Implementasi', 100, 0.25),
(1, 'Dokumentasi', 100, 0.2),
(2, 'Inovasi', 100, 0.35),
(2, 'User Experience', 100, 0.35),
(2, 'Presentasi', 100, 0.3);

-- Insert Score & Score Detail (rekap nilai otomatis bisa dihitung dari total bobot)
INSERT INTO score (submission_id, juri_id, total_score, feedback) VALUES
(1, 2, 85.5, 'Kreatif, namun dokumentasi kurang lengkap'),
(2, 2, 90.0, 'Game menarik, sangat sesuai tema'),
(1, 3, 88.0, 'Bagus, perlu peningkatan UX');

INSERT INTO score_detail (score_id, rubric_criteria_id, score_value) VALUES
(1, 1, 90), (1, 2, 85), (1, 3, 80), (1, 4, 87),
(2, 1, 95), (2, 2, 90), (2, 3, 85), (2, 4, 90),
(3, 1, 88), (3, 2, 90), (3, 3, 85), (3, 4, 89);

-- Insert Selection Result (kelulusan)
INSERT INTO selection_result (team_id, stage_id, is_passed, note) VALUES
(1, 1, TRUE, 'Lolos seleksi administrasi'),
(2, 1, TRUE, 'Lolos'),
(3, 1, FALSE, 'Tidak memenuhi syarat administrasi'),
(1, 2, TRUE, 'Karya bagus, lolos ke bootcamp'),
(2, 2, TRUE, 'Lolos ke bootcamp');

-- Insert Announcement (personalisasi)
INSERT INTO announcement (title, content, target_team_id, target_stage_id, type) VALUES
('Pengumuman Hasil Seleksi Administrasi', 'Selamat! Tim Anda lolos ke tahap seleksi karya.', 1, 1, 'team'),
('Pengumuman Hasil Seleksi Administrasi', 'Mohon maaf, tim Anda belum berhasil melaju.', 3, 1, 'team'),
('Jadwal Bootcamp', 'Bootcamp akan dilaksanakan pada 5-6 Juli 2026 secara hybrid.', NULL, 3, 'stage'),
('Info Lomba', 'Pendaftaran ditutup 31 Mei 2026.', NULL, NULL, 'global');

-- Insert Certificate
INSERT INTO certificate (team_id, event_id, certificate_url) VALUES
(1, 1, '/certificates/Team1_Peserta.pdf'),
(2, 1, '/certificates/Team2_Peserta.pdf');

-- Insert FAQ
INSERT INTO faq (question, answer, display_order) VALUES
('Apakah bisa mengganti anggota tim?', 'Hanya ketua tim yang dapat mengupdate data tim melalui menu Profil Tim.', 1),
('Berapa maksimal file upload?', 'Maksimal 10 file per submission, masing-masing 50MB.', 2),
('Bagaimana sistem penilaian?', 'Juri menilai berdasarkan rubrik yang sudah ditentukan. Nilai akhir dihitung otomatis.', 3);

-- Insert OTP (contoh untuk user 4 = Budi)
INSERT INTO otp (user_id, code, expires_at, is_used) VALUES
(4, '123456', '2026-05-25 10:00:00', FALSE),
(5, '654321', '2026-05-25 11:00:00', TRUE);

-- Insert Audit Log (log aktivitas)
INSERT INTO audit_log (user_id, action, entity_type, entity_id, old_value_json, new_value_json, ip_address) VALUES
(1, 'UPDATE', 'team', 1, '{"institution":"Universitas Indonesia"}', '{"institution":"Universitas Indonesia Depok"}', '192.168.1.10'),
(2, 'CREATE', 'score', 1, NULL, '{"total_score":85.5,"juri_id":2}', '10.0.0.5'),
(4, 'UPDATE', 'submission', 1, '{"description":"Platform edukasi"}', '{"description":"Platform edukasi interaktif"}', '203.0.113.5');

-- =====================================================
-- COMMIT (selesai)
-- =====================================================

---

## 📌 API Endpoints Utama

| Method | Endpoint                            | Role         | Deskripsi                          |
|--------|-------------------------------------|--------------|------------------------------------|
| POST   | /api/register                       | public       | Daftar peserta (ketua tim)         |
| POST   | /api/login                          | public       | Kirim OTP                          |
| POST   | /api/verify-otp                     | public       | Verifikasi OTP → login + cookie    |
| POST   | /api/logout                         | auth         | Logout + hapus cookie               |
| GET    | /api/me                             | auth         | Data user login                    |
| GET    | /api/team                           | peserta      | Lihat tim sendiri                  |
| PUT    | /api/team                           | peserta      | Update tim                         |
| POST   | /api/submissions                    | peserta      | Buat submission                    |
| POST   | /api/submissions/{id}/files         | peserta      | Upload file massal                 |
| POST   | /api/scores                         | juri         | Beri nilai submission              |
| GET    | /api/scores/auto-recap/{submission} | semua        | Rekap rata-rata nilai              |
| GET    | /api/dashboard/admin                | admin        | Statistik global                   |
| GET    | /api/export/teams                   | admin        | Download Excel semua tim           |
| POST   | /api/announcements                  | admin        | Buat pengumuman personalisasi       |
| POST   | /api/certificates/generate/{event}  | admin        | Queue generate sertifikat PDF      |
| GET    | /api/audit-logs                     | admin        | Lihat semua log aktivitas          |

> Lihat file `routes/api.php` untuk daftar lengkap.

---

## 🔧 Troubleshooting Umum

| Error                                  | Solusi                                                                   |
|----------------------------------------|--------------------------------------------------------------------------|
| `Class "Redis" not found`              | Install ekstensi PHP Redis dan restart web server.                      |
| `SQLSTATE[42P01] relation "cache" does not exist` | Set `CACHE_DRIVER=redis` atau jalankan `php artisan cache:table && migrate`. |
| `401 Unauthenticated` pada API          | Pastikan cookie `auth_token` ada di Postman / browser. Cek `SANCTUM_STATEFUL_DOMAINS`. |
| OTP tidak terkirim                     | Gunakan mail driver `log` untuk testing: `MAIL_MAILER=log`. Cek `storage/logs/laravel.log`. |
| Queue worker tidak memproses job       | Jalankan `php artisan queue:work redis` di terminal terpisah.            |

---

## 📜 Lisensi

Proyek ini bersifat internal untuk MPR RI – tidak untuk didistribusikan secara umum.

---

**Dibuat dengan ❤️ oleh Tim Developer CodingMu**
```

---

## 📥 Postman Collection (JSON)

Simpan file berikut sebagai `LMS_Backend.postman_collection.json` dan **import** ke Postman.

```json
{
  "info": {
    "name": "LMS Backend MPR",
    "schema": "https://schema.getpostman.com/json/collection/v2.1.0/collection.json"
  },
  "variable": [
    { "key": "base_url", "value": "http://localhost:8000/api", "type": "string" },
    { "key": "user_id", "value": "", "type": "string" },
    { "key": "submission_id", "value": "", "type": "string" },
    { "key": "team_id", "value": "", "type": "string" }
  ],
  "item": [
    {
      "name": "1. Register",
      "request": {
        "method": "POST",
        "url": "{{base_url}}/register",
        "header": [{ "key": "Content-Type", "value": "application/json" }],
        "body": {
          "mode": "raw",
          "raw": "{\n    \"name\": \"Budi Santoso\",\n    \"email\": \"budi@tim.id\",\n    \"password\": \"rahasia123\"\n}"
        }
      }
    },
    {
      "name": "2. Request OTP",
      "request": {
        "method": "POST",
        "url": "{{base_url}}/request-otp",
        "body": {
          "mode": "raw",
          "raw": "{\n    \"email\": \"budi@tim.id\"\n}"
        }
      }
    },
    {
      "name": "3. Verify OTP & Login",
      "request": {
        "method": "POST",
        "url": "{{base_url}}/verify-otp",
        "body": {
          "mode": "raw",
          "raw": "{\n    \"user_id\": 4,\n    \"code\": \"123456\"\n}"
        }
      }
    },
    {
      "name": "4. Get My Team",
      "request": {
        "method": "GET",
        "url": "{{base_url}}/team"
      }
    },
    {
      "name": "5. Update Team",
      "request": {
        "method": "PUT",
        "url": "{{base_url}}/team",
        "body": {
          "mode": "raw",
          "raw": "{\n    \"team_name\": \"Tim Kreatif Updated\",\n    \"institution\": \"UI Depok\"\n}"
        }
      }
    },
    {
      "name": "6. Create Submission",
      "request": {
        "method": "POST",
        "url": "{{base_url}}/submissions",
        "body": {
          "mode": "raw",
          "raw": "{\n    \"stage_id\": 1,\n    \"project_type\": \"website_application\",\n    \"description\": \"Platform edukasi\"\n}"
        }
      }
    },
    {
      "name": "7. Upload Files (massal)",
      "request": {
        "method": "POST",
        "url": "{{base_url}}/submissions/{{submission_id}}/files",
        "header": [{ "key": "Content-Type", "value": "multipart/form-data" }],
        "body": {
          "mode": "formdata",
          "formdata": [
            { "key": "files[]", "type": "file", "src": "/path/to/file1.pdf" },
            { "key": "files[]", "type": "file", "src": "/path/to/file2.zip" }
          ]
        }
      }
    },
    {
      "name": "8. Juri: Give Score",
      "request": {
        "method": "POST",
        "url": "{{base_url}}/scores",
        "body": {
          "mode": "raw",
          "raw": "{\n    \"submission_id\": 1,\n    \"feedback\": \"Bagus\",\n    \"scores\": [\n        {\"criteria_id\": 1, \"score_value\": 85},\n        {\"criteria_id\": 2, \"score_value\": 90}\n    ]\n}"
        }
      }
    },
    {
      "name": "9. Auto Recap Score",
      "request": {
        "method": "GET",
        "url": "{{base_url}}/scores/auto-recap/{{submission_id}}"
      }
    },
    {
      "name": "10. Admin Dashboard",
      "request": {
        "method": "GET",
        "url": "{{base_url}}/dashboard/admin"
      }
    },
    {
      "name": "11. Export Teams (Excel)",
      "request": {
        "method": "GET",
        "url": "{{base_url}}/export/teams"
      }
    },
    {
      "name": "12. Logout",
      "request": {
        "method": "POST",
        "url": "{{base_url}}/logout"
      }
    }
  ]
}
```

---

## ✅ Step 8 – Full Testing (Manual + Otomatis)

### A. Testing Manual dengan Postman

1. **Import** file JSON di atas ke Postman.
2. **Set environment** `base_url` = `http://localhost:8000/api`.
3. **Urutan run** (sesuai nomor di collection):
   - Register (ambil `user_id` dari response, simpan ke environment).
   - Request OTP (cek log email jika pakai `MAIL_MAILER=log` di `storage/logs/laravel.log`).
   - Verify OTP (gunakan kode dari log).
   - Lanjutkan ke endpoint lain setelah login cookie otomatis tersimpan.

4. **Role switching**:
   - Untuk menguji sebagai juri, login dengan `juri1@codingmu.com` password `juri123` (dari seeder). Lakukan verify OTP.
   - Untuk admin, gunakan `admin@codingmu.com` password `admin123`.

### B. Testing dengan PHPUnit

Buat test dasar di `tests/Feature/Api/`:

```bash
php artisan make:test TeamControllerTest
```

Contoh test ringkas:

```php
<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use App\Models\User;
use App\Models\Team;
use Laravel\Sanctum\Sanctum;

class TeamControllerTest extends TestCase
{
    public function test_team_show()
    {
        $user = User::factory()->create(['role' => 'peserta']);
        $team = Team::factory()->create(['ketua_id' => $user->id]);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/team');
        $response->assertStatus(200)
                 ->assertJson(['team_name' => $team->team_name]);
    }
}
```

Jalankan semua test:

```bash
php artisan test
```

### C. Testing Queue & Job

Pastikan Redis berjalan dan worker aktif:

```bash
php artisan queue:work redis --once
```

Buat request ke `/api/certificates/generate/1` dengan body `{"team_id":1}`. Lihat job diproses.

### D. Testing Cache

- Akses `GET /api/dashboard/admin` dua kali. Lihat log Redis `redis-cli monitor` untuk melihat operasi `get` dan `setex`.
- Update data (misal edit tim) → cache terkait harus terhapus.

---

## 🎯 Kesimpulan

Dengan README ini, Anda memiliki panduan **end-to-end** untuk menjalankan, menguji, dan mendeploy backend LMS. Semua fitur yang di-request (riwayat perubahan, rubrik dinamis, rekap otomatis, upload massal, personalisasi pengumuman, dashboard, export, sertifikat, OTP, audit log, restore data, dan redis cache) telah terintegrasi.

Jika ada pertanyaan atau error, cek bagian Troubleshooting atau buka issue di repository GitHub.