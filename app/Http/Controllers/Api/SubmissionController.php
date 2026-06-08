<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Submission;
use App\Models\SubmissionFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class SubmissionController extends Controller
{
    // Buat submission baru (draft)
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

        // Cek apakah sudah ada submission untuk stage ini
        $existing = Submission::where('team_id', $team->id)->where('stage_id', $request->stage_id)->first();
        if ($existing) {
            return response()->json(['message' => 'Sudah ada submission untuk tahap ini', 'submission' => $existing], 409);
        }

        $submission = Submission::create([
            'team_id' => $team->id,
            'stage_id' => $request->stage_id,
            'project_type' => $request->project_type,
            'description' => $request->description,
            'status' => 'draft',
        ]);

        return response()->json($submission, 201);
    }

    // Tampilkan submission + file/link
    public function show(Submission $submission)
    {
        $this->authorizeTeam($submission->team_id);
        return response()->json($submission->load(['files', 'stage']));
    }

    // Update submission (deskripsi, project_type)
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

    // Upload file (multiple) untuk submission
    public function uploadFiles(Request $request, Submission $submission)
    {
        $this->authorizeTeam($submission->team_id);
        $request->validate([
            'files' => 'required|array|max:10',
            'files.*' => 'file|max:51200', // 50MB
        ]);

        $uploaded = [];
        foreach ($request->file('files') as $file) {
            $originalName = $file->getClientOriginalName();
            $mime = $file->getMimeType();
            $path = $file->store('submissions/' . $submission->id, 'public');
            $fileUrl = Storage::url($path);
            
            $submissionFile = SubmissionFile::create([
                'submission_id' => $submission->id,
                'file_url' => $fileUrl,
                'file_name' => $originalName,
                'file_size' => $file->getSize(),
                'file_type' => 'file',
                'mime_type' => $mime,
                'file_path' => $path,
                'is_verified' => false,
            ]);
            $uploaded[] = $submissionFile;
        }
        return response()->json($uploaded, 201);
    }

    // Tambah link (GitHub, Google Drive, YouTube, dll)
    public function addLink(Request $request, Submission $submission)
    {
        $this->authorizeTeam($submission->team_id);
        $request->validate([
            'url' => 'required|url|max:500',
            'description' => 'nullable|string|max:255',
        ]);

        $submissionFile = SubmissionFile::create([
            'submission_id' => $submission->id,
            'file_url' => $request->url,
            'file_name' => $request->description ?? 'External Link',
            'file_size' => null,
            'file_type' => 'link',
            'external_url' => $request->url,
            'mime_type' => 'link',
            'file_path' => null,
            'is_verified' => false,
        ]);

        return response()->json($submissionFile, 201);
    }

    // Hapus file/link
    public function deleteFile(SubmissionFile $file)
    {
        $this->authorizeTeam($file->submission->team_id);
        if ($file->file_type === 'file' && $file->file_path) {
            Storage::disk('public')->delete($file->file_path);
        }
        $file->delete();
        return response()->json(['message' => 'File/link dihapus']);
    }

    // Submit final (ubah status jadi submitted)
    public function submit(Submission $submission)
    {
        $this->authorizeTeam($submission->team_id);
        if ($submission->status === 'submitted') {
            return response()->json(['message' => 'Sudah disubmit'], 400);
        }
        // Pastikan minimal ada 1 file atau link
        if ($submission->files()->count() === 0) {
            return response()->json(['message' => 'Harap upload minimal 1 file atau link sebelum submit'], 422);
        }
        $submission->update([
            'status' => 'submitted',
            'submitted_at' => now(),
        ]);
        return response()->json(['message' => 'Submission berhasil dikirim', 'submission' => $submission]);
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