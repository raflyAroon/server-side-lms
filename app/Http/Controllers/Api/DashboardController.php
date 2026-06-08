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

        public function peserta(Request $request)
    {
        Log::info('Dashboard peserta dipanggil', ['user_id' => $request->user()?->id]);

        try {
            $user = $request->user();
            if (!$user) {
                return response()->json(['message' => 'Unauthenticated'], 401);
            }

            $team = Team::where('ketua_id', $user->id)
                ->orWhereHas('anggota', fn($q) => $q->where('user_id', $user->id))
                ->first();

            if (!$team) {
                return response()->json([
                    'progress' => '0%',
                    'points' => '0',
                    'rank' => '#N/A',
                    'team_name' => 'Belum ada tim',
                    'team_members' => [],
                ]);
            }

            $submission = Submission::where('team_id', $team->id)->latest()->first();
            $progress = $submission ? '50%' : '0%';

            $teamMembers = $team->members->map(fn($a) => [
                'name' => $a->name,
                'email' => $a->email,
                'role' => 'Anggota',
            ]);

            return response()->json([
                'progress' => $progress,
                'points' => '1.250',
                'rank' => '#12',
                'team_name' => $team->team_name,
                'team_members' => $teamMembers,
            ]);
        } catch (\Exception $e) {
            Log::error('Error di DashboardController@peserta: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => 'Terjadi kesalahan server',
                'debug' => $e->getMessage() // hanya untuk development
            ], 500);
        }
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