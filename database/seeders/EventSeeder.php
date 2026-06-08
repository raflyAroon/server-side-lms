<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Event;
use App\Models\Stage;
use App\Models\Rubric;
use App\Models\RubricCriterion;

class EventSeeder extends Seeder
{
    public function run(): void
    {
        // Event utama
        $event = Event::create([
            'name' => 'CodingMu x MPR RI Hackathon 2026',
            'description' => 'Hackathon 4 Pilar Kebangsaan',
            'start_date' => '2026-07-01',
            'end_date' => '2026-12-31',
        ]);

        // Tahapan
        $stage1 = Stage::create(['event_id' => $event->id, 'name' => 'Seleksi Portofolio', 'stage_order' => 1, 'is_active' => true]);
        $stage2 = Stage::create(['event_id' => $event->id, 'name' => 'Hackathon 1 (Top 30)', 'stage_order' => 2, 'is_active' => false]);
        $stage3 = Stage::create(['event_id' => $event->id, 'name' => 'Hackathon 2 (Top 15 Semi Final & Final)', 'stage_order' => 3, 'is_active' => false]);

        // Rubrik untuk seleksi portofolio
        $rubric = Rubric::create(['stage_id' => $stage1->id, 'name' => 'Rubrik Penilaian Portofolio', 'description' => 'Penilaian awal berdasarkan portofolio']);
        
        $criteria = [
            ['criterion_name' => 'Kreativitas Ide', 'max_score' => 25, 'weight' => 1.0],
            ['criterion_name' => 'Teknis Implementasi', 'max_score' => 25, 'weight' => 1.0],
            ['criterion_name' => 'Kesesuaian Tema', 'max_score' => 25, 'weight' => 1.0],
            ['criterion_name' => 'Kelengkapan Dokumen', 'max_score' => 25, 'weight' => 1.0],
        ];
        foreach ($criteria as $c) {
            RubricCriterion::create(array_merge($c, ['rubric_id' => $rubric->id]));
        }
    }
}