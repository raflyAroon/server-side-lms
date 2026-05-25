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

**Happy Coding! 🚀**
```

---

**Catatan:**  
- Ganti `username/lms-backend.git` dengan repository GitHub asli Anda.  
- File Postman collection di atas bisa langsung di-copy sebagai file `.json`.  
- Untuk production, pastikan `APP_DEBUG=false`, `QUEUE_CONNECTION=redis`, dan gunakan supervisor untuk menjaga worker tetap hidup.