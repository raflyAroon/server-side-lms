<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Score;
use App\Models\ScoreDetail;
use App\Models\Submission;
use App\Models\RubricCriteria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ScoreController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'submission_id' => 'required|exists:submissions,id',
            'scores' => 'required|array',
            'scores.*.criteria_id' => 'required|exists:rubric_criteria,id',
            'scores.*.score_value' => 'required|numeric|min:0|max:100',
            'feedback' => 'nullable|string',
        ]);

        $user = Auth::user();
        if ($user->role !== 'juri') {
            return response()->json(['message' => 'Only juri can score'], 403);
        }

        $submission = Submission::findOrFail($request->submission_id);
        // Cek apakah juri sudah memberi nilai untuk submission ini
        $existing = Score::where('submission_id', $submission->id)
                        ->where('juri_id', $user->id)
                        ->first();
        if ($existing) {
            return response()->json(['message' => 'Anda sudah menilai submission ini'], 409);
        }

        $score = Score::create([
            'submission_id' => $submission->id,
            'juri_id' => $user->id,
            'total_score' => 0, // sementara, akan dihitung otomatis oleh observer
            'feedback' => $request->feedback,
        ]);

        foreach ($request->scores as $item) {
            ScoreDetail::create([
                'score_id' => $score->id,
                'rubric_criteria_id' => $item['criteria_id'],
                'score_value' => $item['score_value'],
            ]);
        }

        // Recalculate total score via observer (akan otomatis saat saved)
        // Panggil save untuk trigger observer? sudah ter-create.
        // Tapi observer saved akan dipanggil setelah create, jadi otomatis.

        return response()->json($score->load('details'), 201);
    }

    public function getBySubmission($submissionId)
    {
        $scores = Score::where('submission_id', $submissionId)
                       ->with('juri', 'details.rubricCriteria')
                       ->get();
        return response()->json($scores);
    }

    public function autoRecap($submissionId)
    {
        $scores = Score::where('submission_id', $submissionId)->get();
        $totalAverage = $scores->avg('total_score');
        return response()->json([
            'submission_id' => $submissionId,
            'average_score' => round($totalAverage, 2),
            'scores' => $scores
        ]);
    }
}