<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminTeamController extends Controller
{
    public function index(Request $request)
    {
        $query = Team::with(['ketua', 'members', 'documents']);
        
        if ($request->has('status')) {
            $query->where('selection_status', $request->status);
        }
        
        $teams = $query->orderBy('created_at', 'desc')->paginate(20);
        return response()->json($teams);
    }

    public function show($id)
    {
        $team = Team::with(['ketua', 'members', 'documents'])->findOrFail($id);
        return response()->json($team);
    }

    public function updateSelection(Request $request, $id)
    {
        $request->validate([
            'selection_status' => 'required|in:approved,rejected',
            'selection_note' => 'nullable|string|max:500',
        ]);

        $team = Team::findOrFail($id);
        
        DB::beginTransaction();
        try {
            $team->update([
                'selection_status' => $request->selection_status,
                'selection_note' => $request->selection_note,
                'selection_processed_at' => now(),
            ]);
            
            // Clear cache jika ada
            if ($team->ketua_id) {
                cache()->forget("dashboard:peserta:{$team->ketua_id}");
            }
            
            DB::commit();
            return response()->json(['message' => 'Status seleksi diperbarui', 'team' => $team]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal update: ' . $e->getMessage()], 500);
        }
    }
}