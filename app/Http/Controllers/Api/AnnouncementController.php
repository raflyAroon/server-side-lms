<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Traits\Cacheable;

class AnnouncementController extends Controller
{
    use Cacheable;

    public function index()
    {
        $user = Auth::user();
        $teamId = optional($user->teamAsKetua)->id ?? 0;
        $stageId = optional($user->teamAsKetua?->submissions()->latest()->first())->stage_id;

        $announcements = $this->rememberAnnouncements($teamId, $stageId);
        return response()->json($announcements);
    }

    public function store(Request $request)
    {
        $this->authorize('admin');
        $request->validate([
            'title' => 'required|string|max:200',
            'content' => 'required|string',
            'type' => 'required|in:global,stage,team',
            'target_team_id' => 'nullable|exists:teams,id',
            'target_stage_id' => 'nullable|exists:stages,id',
        ]);
        $announcement = Announcement::create($request->all());

        // Clear cache
        $this->cache()->forgetPattern('announcements:*');

        return response()->json($announcement, 201);
    }

    public function update(Request $request, Announcement $announcement)
    {
        $this->authorize('admin');
        $announcement->update($request->only(['title', 'content']));
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