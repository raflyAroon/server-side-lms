<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Team;
use App\Models\TeamHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Traits\Cacheable;

class TeamController extends Controller
{
    use Cacheable;

    public function show()
    {
        $user = Auth::user();
        $team = Team::where('ketua_id', $user->id)->firstOrFail();
        // Gunakan cache untuk team
        $cachedTeam = $this->rememberTeam($team->id);
        return response()->json($cachedTeam);
    }

    public function update(Request $request)
    {
        $user = Auth::user();
        $team = Team::where('ketua_id', $user->id)->firstOrFail();
        $validated = $request->validate([
            'team_name' => 'sometimes|string|max:100',
            'institution' => 'nullable|string|max:200',
        ]);
        $team->update($validated);

        // Clear cache
        $this->forgetTeam($team->id);
        $this->forgetDashboard('peserta', $user->id);
        $this->forgetAnnouncementsByTeam($team->id);

        return response()->json($team);
    }

    public function history()
    {
        $user = Auth::user();
        $team = Team::where('ketua_id', $user->id)->firstOrFail();
        return response()->json($team->histories()->orderBy('changed_at', 'desc')->get());
    }

    public function restore($historyId)
    {
        $user = Auth::user();
        $team = Team::where('ketua_id', $user->id)->firstOrFail();
        $history = TeamHistory::where('team_id', $team->id)->findOrFail($historyId);
        $snapshot = $history->snapshot_data;
        $team->update([
            'team_name' => $snapshot['team_name'] ?? $team->team_name,
            'institution' => $snapshot['institution'] ?? $team->institution,
        ]);

        // Clear cache
        $this->forgetTeam($team->id);
        $this->forgetDashboard('peserta', $user->id);
        $this->forgetAnnouncementsByTeam($team->id);

        return response()->json(['message' => 'Team restored from history', 'team' => $team]);
    }
}