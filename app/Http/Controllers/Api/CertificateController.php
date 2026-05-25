<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use App\Models\Team;
use App\Jobs\GenerateCertificateJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CertificateController extends Controller
{
    public function getByTeam($teamId)
    {
        $user = Auth::user();
        if ($user->role !== 'admin' && optional($user->teamAsKetua)->id != $teamId) {
            return response()->json(['message' => 'Forbidden'], 403);
        }
        $certificates = Certificate::where('team_id', $teamId)->with('event')->get();
        return response()->json($certificates);
    }

    public function generate(Request $request, $eventId)
    {
        $this->authorize('admin');
        $request->validate([
            'team_id' => 'required|exists:teams,id',
        ]);
        $team = Team::findOrFail($request->team_id);
        GenerateCertificateJob::dispatch($team, $eventId);
        return response()->json(['message' => 'Certificate generation queued']);
    }
}