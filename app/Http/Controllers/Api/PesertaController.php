<?php
// app/Http/Controllers/Api/PesertaController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Team;
use App\Models\TeamMember;
use App\Models\Submission;
use App\Models\SubmissionFile;
use App\Models\Stage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PesertaController extends Controller
{
    // ==================== SIDEBAR 1: STATUS TIM ====================
    public function teamStatus()
    {
        $user = Auth::user();
        $team = Team::with('members')->where('ketua_id', $user->id)->firstOrFail();

        return response()->json([
            'team' => [
                'id' => $team->id,
                'team_name' => $team->team_name,
                'selection_status' => $team->selection_status,
                'selection_note' => $team->selection_note,
            ],
            'members' => $team->members->map(fn($m) => [
                'id' => $m->id,
                'name' => $m->name,
                'position' => $m->position,
                'shirt_size' => $m->shirt_size,
            ])
        ]);
    }

    // Konfirmasi lolos seleksi (isi shirt size)
    public function confirmLolosSeleksi(Request $request)
    {
        $user = Auth::user();
        $team = Team::where('ketua_id', $user->id)->firstOrFail();

        if (!$team->canConfirmLolosSeleksi()) {
            return response()->json(['message' => 'Status tim tidak sesuai untuk konfirmasi ini'], 422);
        }

        $request->validate([
            'members' => 'required|array|min:1',
            'members.*.member_id' => 'required|exists:team_members,id',
            'members.*.shirt_size' => 'required|string|in:XS,S,M,L,XL,XXL',
        ]);

        DB::beginTransaction();
        try {
            foreach ($request->members as $item) {
                TeamMember::where('id', $item['member_id'])
                    ->where('team_id', $team->id)
                    ->update(['shirt_size' => $item['shirt_size']]);
            }

            $team->update([
                'selection_status' => 'follow_the_bootcamp',
                'selection_processed_at' => now(),
            ]);

            DB::commit();
            return response()->json(['message' => 'Konfirmasi berhasil, selamat mengikuti Bootcamp!']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal: ' . $e->getMessage()], 500);
        }
    }

    // Konfirmasi bootcamp (pilih project type)
    public function confirmBootcamp(Request $request)
    {
        $user = Auth::user();
        $team = Team::where('ketua_id', $user->id)->firstOrFail();

        if (!$team->canConfirmBootcamp()) {
            return response()->json(['message' => 'Status tim tidak sesuai'], 422);
        }

        $request->validate([
            'project_type' => 'required|in:AI Application,Game Dev,Video Animation',
            'description' => 'nullable|string',
        ]);

        // Cari stage hackathon aktif (first half)
        $stage = Stage::where('name', 'LIKE', '%first half%')
            ->orWhere('name', 'LIKE', '%hackathon%')
            ->where('is_active', true)
            ->first();

        if (!$stage) {
            return response()->json(['message' => 'Tahap hackathon belum tersedia'], 422);
        }

        DB::beginTransaction();
        try {
            $submission = Submission::create([
                'team_id' => $team->id,
                'stage_id' => $stage->id,
                'project_type' => $request->project_type,
                'description' => $request->description,
                'status' => 'draft',
            ]);

            $team->update(['selection_status' => 'first_half_hackathon']);

            DB::commit();
            return response()->json([
                'message' => 'Konfirmasi berhasil!',
                'submission_id' => $submission->id
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal: ' . $e->getMessage()], 500);
        }
    }

    // ==================== SIDEBAR 2: PROFIL TIM ====================
    public function teamProfile()
    {
        $user = Auth::user();
        $team = Team::with(['members', 'documents'])
            ->where('ketua_id', $user->id)
            ->firstOrFail();

        $ketua = $user->only(['id', 'name', 'email']);

        return response()->json([
            'team' => $team->only(['id', 'team_name', 'institution', 'city', 'selection_status']),
            'members' => $team->members,
            'documents' => $team->documents,
            'ketua' => $ketua,
        ]);
    }

    // Update profil tim (optional, bisa dikembangkan nanti)
    public function updateTeamProfile(Request $request)
    {
        // Implementasi serupa dengan TeamController@update, tapi khusus untuk peserta
        // (tidak diimplementasikan detail di sini karena waktu)
        return response()->json(['message' => 'Fitur update profil tim akan segera hadir'], 501);
    }

    // ==================== SIDEBAR 3: HACKATHON SUBMISSIONS ====================
    public function getSubmissions()
    {
        $user = Auth::user();
        $team = Team::where('ketua_id', $user->id)->firstOrFail();

        $submissions = Submission::with(['stage', 'files'])
            ->where('team_id', $team->id)
            ->orderBy('stage_id')
            ->get()
            ->map(function ($sub) {
                return [
                    'id' => $sub->id,
                    'stage_id' => $sub->stage_id,
                    'stage_name' => $sub->stage->name,
                    'project_type' => $sub->project_type,
                    'status' => $sub->status,
                    'submitted_at' => $sub->submitted_at,
                    'files' => $sub->files->map(fn($f) => [
                        'id' => $f->id,
                        'submission_category' => $f->submission_category,
                        'file_url' => $f->file_url,
                        'file_name' => $f->file_name,
                        'file_type' => $f->file_type,
                        'external_url' => $f->external_url,
                        'is_verified' => $f->is_verified,
                    ]),
                ];
            });

        return response()->json([
            'project_type' => $team->submissions()->first()?->project_type,
            'submissions' => $submissions,
            'current_status' => $team->selection_status,
        ]);
    }

    // Upload file ke submission (logbook atau final)
    public function uploadSubmissionFile(Request $request, Submission $submission)
    {
        $user = Auth::user();
        $team = Team::where('ketua_id', $user->id)->firstOrFail();

        if ($submission->team_id !== $team->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'submission_category' => 'required|in:logbook_1,logbook_2,final_submission',
            'file_type' => 'required|in:file,link',
            'file' => 'required_if:file_type,file|file|max:51200',
            'external_url' => 'required_if:file_type,link|url',
        ]);

        DB::beginTransaction();
        try {
            $fileData = [
                'submission_id' => $submission->id,
                'submission_category' => $request->submission_category,
                'file_type' => $request->file_type,
                'is_verified' => false,
            ];

            if ($request->file_type === 'file') {
                $file = $request->file('file');
                $path = $file->store("submissions/{$submission->id}", 'public');
                $fileData['file_url'] = Storage::url($path);
                $fileData['file_name'] = $file->getClientOriginalName();
                $fileData['file_size'] = $file->getSize();
                $fileData['mime_type'] = $file->getMimeType();
                $fileData['file_path'] = $path;
            } else {
                $fileData['external_url'] = $request->external_url;
                $fileData['file_url'] = $request->external_url;
                $fileData['file_name'] = $request->external_url;
            }

            $submissionFile = SubmissionFile::create($fileData);

            DB::commit();
            return response()->json($submissionFile, 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal upload: ' . $e->getMessage()], 500);
        }
    }

    // Submit final (kunci submission)
    public function submitSubmission(Submission $submission)
    {
        $user = Auth::user();
        $team = Team::where('ketua_id', $user->id)->firstOrFail();

        if ($submission->team_id !== $team->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($submission->status !== 'draft') {
            return response()->json(['message' => 'Submission sudah disubmit sebelumnya'], 422);
        }

        // Validasi berdasarkan project_type dan submission_category
        $projectType = $submission->project_type;
        $files = $submission->files()->where('submission_category', 'final_submission')->get();

        $errors = [];
        if ($projectType === 'AI Application') {
            $hasSource = $files->contains(fn($f) => str_contains($f->file_name, 'source') || str_contains($f->external_url ?? '', 'github'));
            $hasVideoDemo = $files->contains(fn($f) => str_contains($f->file_name, 'video') || str_contains($f->external_url ?? '', 'youtube'));
            $hasHosting = $files->contains(fn($f) => str_contains($f->file_name, 'hosting') || str_contains($f->external_url ?? '', 'http'));
            if (!$hasSource) $errors[] = 'Upload source code (file/link GitHub)';
            if (!$hasVideoDemo) $errors[] = 'Upload video demo (link YouTube)';
            if (!$hasHosting) $errors[] = 'Upload link hosting/deployment';
        } elseif ($projectType === 'Game Dev') {
            $hasSourceLink = $files->contains(fn($f) => str_contains($f->external_url ?? '', 'github'));
            $hasBuildFile = $files->contains(fn($f) => $f->file_type === 'file' && str_ends_with($f->file_name, '.application'));
            $hasVideoDemo = $files->contains(fn($f) => str_contains($f->external_url ?? '', 'youtube'));
            if (!$hasSourceLink) $errors[] = 'Link source code (GitHub)';
            if (!$hasBuildFile) $errors[] = 'Build file (.application)';
            if (!$hasVideoDemo) $errors[] = 'Video demo (link)';
        } elseif ($projectType === 'Video Animation') {
            $hasVideoFile = $files->contains(fn($f) => $f->file_type === 'file' && str_ends_with($f->file_name, '.mp4'));
            $hasVideoDemo = $files->contains(fn($f) => str_contains($f->external_url ?? '', 'youtube'));
            if (!$hasVideoFile) $errors[] = 'Upload file video (.mp4)';
            if (!$hasVideoDemo) $errors[] = 'Video demo (link)';
        }

        if (!empty($errors)) {
            return response()->json(['message' => 'File wajib belum lengkap', 'missing' => $errors], 422);
        }

        $submission->update([
            'status' => 'submitted',
            'submitted_at' => now(),
        ]);

        return response()->json(['message' => 'Submission berhasil dikumpulkan']);
    }
}