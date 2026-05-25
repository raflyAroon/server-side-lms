<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Submission;
use App\Models\SubmissionFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class SubmissionController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'stage_id' => 'required|exists:stages,id',
            'project_type' => 'required|in:website_application,game_development,video_design',
            'description' => 'nullable|string',
        ]);

        $user = Auth::user();
        $team = $user->teamAsKetua;
        if (!$team) {
            return response()->json(['message' => 'Anda belum memiliki tim'], 403);
        }

        $submission = Submission::create([
            'team_id' => $team->id,
            'stage_id' => $request->stage_id,
            'project_type' => $request->project_type,
            'description' => $request->description,
        ]);

        return response()->json($submission, 201);
    }

    public function show(Submission $submission)
    {
        $this->authorizeTeam($submission->team_id);
        return response()->json($submission->load('files', 'stage'));
    }

    public function update(Request $request, Submission $submission)
    {
        $this->authorizeTeam($submission->team_id);
        $request->validate([
            'description' => 'nullable|string',
            'project_type' => 'sometimes|in:website_application,game_development,video_design',
        ]);
        $submission->update($request->only(['description', 'project_type']));
        return response()->json($submission);
    }

    public function uploadFiles(Request $request, Submission $submission)
    {
        $this->authorizeTeam($submission->team_id);
        $request->validate([
            'files' => 'required|array|max:10',
            'files.*' => 'file|max:51200', // 50MB each
        ]);

        $uploaded = [];
        foreach ($request->file('files') as $file) {
            $path = $file->store('submissions/' . $submission->id, 'public');
            $submissionFile = SubmissionFile::create([
                'submission_id' => $submission->id,
                'file_url' => Storage::url($path),
                'file_name' => $file->getClientOriginalName(),
                'file_size' => $file->getSize(),
            ]);
            $uploaded[] = $submissionFile;
        }
        return response()->json($uploaded, 201);
    }

    public function deleteFile(SubmissionFile $file)
    {
        $this->authorizeTeam($file->submission->team_id);
        Storage::disk('public')->delete(str_replace('/storage/', '', $file->file_url));
        $file->delete();
        return response()->json(['message' => 'File deleted']);
    }

    private function authorizeTeam($teamId)
    {
        $user = Auth::user();
        $team = $user->teamAsKetua;
        if (!$team || $team->id != $teamId) {
            abort(403, 'Unauthorized');
        }
    }
}