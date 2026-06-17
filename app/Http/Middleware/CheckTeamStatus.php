<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Team;

class CheckTeamStatus
{
    public function handle(Request $request, Closure $next, ...$allowedStatuses)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $team = Team::where('ketua_id', $user->id)->first();
        if (!$team) {
            return response()->json(['message' => 'Tim tidak ditemukan'], 404);
        }

        // Jika tidak ada allowedStatuses, izinkan akses
        if (!empty($allowedStatuses) && !in_array($team->selection_status, $allowedStatuses)) {
            return response()->json([
                'message' => 'Status tim tidak mengizinkan aksi ini',
                'current_status' => $team->selection_status
            ], 403);
        }

        $request->merge(['team' => $team]); // simpan team ke request
        return $next($request);
    }
}