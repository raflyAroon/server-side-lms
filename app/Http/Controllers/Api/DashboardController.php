<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Team;
use App\Models\Submission;
use App\Models\Score;
use App\Models\Stage;
use Illuminate\Support\Facades\Auth;
use App\Traits\Cacheable;

class DashboardController extends Controller
{
    use Cacheable;

    public function peserta()
    {
        $user = Auth::user();
        $team = $user->teamAsKetua;
        if (!$team) {
            return response()->json(['message' => 'Tim tidak ditemukan'], 404);
        }

        $data = $this->rememberDashboard('peserta', $user->id, function () use ($team) {
            $submissions = Submission::where('team_id', $team->id)->with('stage')->get();
            $latestStage = $submissions->sortByDesc('stage.stage_order')->first();
            $scores = Score::whereIn('submission_id', $submissions->pluck('id'))->with('juri')->get();
            return [
                'team' => $team,
                'submissions' => $submissions,
                'current_stage' => $latestStage?->stage,
                'average_score' => $scores->avg('total_score'),
                'scores' => $scores,
            ];
        });

        return response()->json($data);
    }

    public function admin()
    {
        $user = Auth::user();
        $data = $this->rememberDashboard('admin', $user->id, function () {
            return [
                'total_teams' => Team::count(),
                'total_submissions' => Submission::count(),
                'total_scores' => Score::count(),
                'stages' => Stage::withCount('submissions')->get(),
            ];
        });
        return response()->json($data);
    }

    public function juri()
    {
        $user = Auth::user();
        $data = $this->rememberDashboard('juri', $user->id, function () use ($user) {
            $scores = Score::where('juri_id', $user->id)->with('submission.team')->get();
            $pendingSubmissions = Submission::whereDoesntHave('scores', function ($q) use ($user) {
                $q->where('juri_id', $user->id);
            })->with('team', 'stage')->get();
            return [
                'my_scores' => $scores,
                'pending_submissions' => $pendingSubmissions,
            ];
        });
        return response()->json($data);
    }
}