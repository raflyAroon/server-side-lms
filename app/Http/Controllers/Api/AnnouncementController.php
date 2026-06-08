<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Traits\Cacheable;

class AnnouncementController extends Controller
{
    public function index()
    {
        try {
            $user = Auth::user();
            $query = Announcement::where('published_at', '<=', now())
                ->orderBy('published_at', 'desc');

            if (!$user) {
                // Publik: hanya global (tidak terikat team/stage)
                $query->whereNull('target_team_id')->whereNull('target_stage_id');
            } else {
                $teamId = optional($user->teamAsKetua)->id;
                $stageId = optional($user->teamAsKetua?->submissions()->latest()->first())->stage_id;
                $query->where(function ($q) use ($teamId, $stageId) {
                    $q->whereNull('target_team_id')->whereNull('target_stage_id');
                    if ($teamId) $q->orWhere('target_team_id', $teamId);
                    if ($stageId) $q->orWhere('target_stage_id', $stageId);
                });
            }

            return response()->json($query->get());
        } catch (\Exception $e) {
            Log::error('Announcement API error: ' . $e->getMessage());
            return response()->json(['error' => 'Terjadi kesalahan server'], 500);
        }
    }

    // store, update, destroy sama seperti sebelumnya (tidak berubah)
    public function store(Request $request)
    {
        $this->authorize('admin');
        $request->validate([
            'title' => 'required|string|max:200',
            'content' => 'required|string',
            'type' => 'required|in:global,stage,team',
            'target_team_id' => 'nullable|exists:teams,id',
            'target_stage_id' => 'nullable|exists:stages,id',
            'published_at' => 'nullable|date',
        ]);
        $announcement = Announcement::create($request->all());
        $this->cache()->forgetPattern('announcements:*');
        return response()->json($announcement, 201);
    }

    public function update(Request $request, Announcement $announcement)
    {
        $this->authorize('admin');
        $announcement->update($request->only(['title', 'content', 'published_at']));
        $this->cache()->forgetPattern('announcements:*');
        return response()->json($announcement);
    }

    public function destroy(Announcement $announcement)
    {
        $this->authorize('admin');
        $announcement->delete();
        $this->cache()->forgetPattern('announcements:*');
        return response()->json(['message' => 'Deleted']);
    }
}