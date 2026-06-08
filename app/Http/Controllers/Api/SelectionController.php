<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Stage;
use App\Models\Submission;
use App\Models\SelectionResult;
use App\Models\Score;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SelectionController extends Controller
{
    // Untuk admin: hitung rata-rata skor submission & tentukan lolos
    public function processSelection(Stage $stage)
    {
        // Pastikan stage memiliki rubrik dan sudah dinilai oleh juri
        $submissions = Submission::where('stage_id', $stage->id)
            ->where('status', 'submitted')
            ->with('scores')
            ->get();

        $results = [];
        foreach ($submissions as $submission) {
            $avgScore = $submission->scores->avg('total_score') ?? 0;
            $results[] = [
                'submission_id' => $submission->id,
                'team_id' => $submission->team_id,
                'score' => $avgScore,
            ];
        }
        // Urutkan berdasarkan skor tertinggi
        usort($results, fn($a, $b) => $b['score'] <=> $a['score']);

        // Tentukan jumlah lolos berdasarkan urutan stage
        $passedCount = ($stage->stage_order == 1) ? 30 : (($stage->stage_order == 2) ? 15 : 0);
        $passedTeamIds = [];
        foreach ($results as $index => $result) {
            $isPassed = ($index + 1) <= $passedCount;
            if ($isPassed) {
                $passedTeamIds[] = $result['team_id'];
            }
            SelectionResult::updateOrCreate(
                ['team_id' => $result['team_id'], 'stage_id' => $stage->id],
                [
                    'is_passed' => $isPassed,
                    'note' => $isPassed ? "Lolos ke tahap berikutnya (skor: {$result['score']})" : "Tidak lolos (skor: {$result['score']})",
                    'announced_at' => now(),
                ]
            );
        }

        // Aktifkan stage berikutnya jika ada
        $nextStage = Stage::where('event_id', $stage->event_id)->where('stage_order', $stage->stage_order + 1)->first();
        if ($nextStage) {
            $nextStage->update(['is_active' => true]);
        }

        return response()->json([
            'message' => "Seleksi selesai. $passedCount tim lolos.",
            'passed_team_ids' => $passedTeamIds,
        ]);
    }

    // Lihat hasil seleksi untuk suatu stage
    public function getResults(Stage $stage)
    {
        $results = SelectionResult::where('stage_id', $stage->id)
            ->with('team')
            ->get();
        return response()->json($results);
    }
}