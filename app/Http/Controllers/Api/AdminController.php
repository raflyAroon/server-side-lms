<?php
// app/Http/Controllers/Api/AdminController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Team;
use App\Models\TeamMember;
use App\Models\Submission;
use App\Models\SelectionResult;
use App\Models\Announcement;
use App\Models\Stage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    // List tim dengan filter status
    public function listTeams(Request $request)
    {
        $query = Team::with('ketua')->orderBy('created_at', 'desc');
        if ($request->has('status') && in_array($request->status, ['pending', 'lolos_seleksi', 'follow_the_bootcamp', 'first_half_hackathon', 'semi_final', 'final', 'rejected'])) {
            $query->where('selection_status', $request->status);
        }
        $teams = $query->paginate(20);
        return response()->json($teams);
    }

    // Detail tim
    public function showTeam($id)
    {
        $team = Team::with(['ketua', 'members', 'documents'])->findOrFail($id);
        return response()->json($team);
    }

    // Approve tim (pending -> lolos_seleksi)
    public function approveTeam(Request $request, $id)
    {
        $team = Team::findOrFail($id);
        if ($team->selection_status !== 'pending') {
            return response()->json(['message' => 'Tim sudah diproses sebelumnya'], 422);
        }

        $request->validate(['note' => 'nullable|string']);

        DB::beginTransaction();
        try {
            // Simpan hasil seleksi
            $stage = Stage::where('stage_order', 1)->first(); // stage seleksi administrasi
            SelectionResult::create([
                'team_id' => $team->id,
                'stage_id' => $stage->id,
                'is_passed' => true,
                'note' => $request->note,
                'announced_at' => now(),
            ]);

            $team->update([
                'selection_status' => 'lolos_seleksi',
                'selection_note' => $request->note,
                'selection_processed_at' => now(),
            ]);

            // Buat pengumuman untuk tim
            Announcement::create([
                'title' => 'Pengumuman Seleksi Administrasi',
                'content' => "Selamat! Tim Anda dinyatakan lolos seleksi administrasi. Silakan masuk ke dashboard untuk konfirmasi lanjut.\nCatatan: " . ($request->note ?? '-'),
                'target_team_id' => $team->id,
                'type' => 'team',
                'published_at' => now(),
            ]);

            DB::commit();
            return response()->json(['message' => 'Tim berhasil disetujui']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal: ' . $e->getMessage()], 500);
        }
    }

    // Reject tim
    public function rejectTeam(Request $request, $id)
    {
        $team = Team::findOrFail($id);
        if ($team->selection_status !== 'pending') {
            return response()->json(['message' => 'Tim sudah diproses'], 422);
        }

        $request->validate(['note' => 'required|string']);

        DB::beginTransaction();
        try {
            $stage = Stage::where('stage_order', 1)->first();
            SelectionResult::create([
                'team_id' => $team->id,
                'stage_id' => $stage->id,
                'is_passed' => false,
                'note' => $request->note,
                'announced_at' => now(),
            ]);

            $team->update([
                'selection_status' => 'rejected',
                'selection_note' => $request->note,
                'selection_processed_at' => now(),
            ]);

            Announcement::create([
                'title' => 'Pengumuman Seleksi Administrasi',
                'content' => "Mohon maaf, tim Anda tidak lolos seleksi administrasi.\nAlasan: " . $request->note,
                'target_team_id' => $team->id,
                'type' => 'team',
                'published_at' => now(),
            ]);

            DB::commit();
            return response()->json(['message' => 'Tim ditolak']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal: ' . $e->getMessage()], 500);
        }
    }

    // Lihat submission tim
    public function teamSubmissions($teamId)
    {
        $team = Team::with('submissions.stage', 'submissions.files')->findOrFail($teamId);
        return response()->json([
            'team' => $team->only(['id', 'team_name', 'selection_status']),
            'submissions' => $team->submissions,
        ]);
    }

    // Review submission (approve/reject setelah penjurian)
    public function reviewSubmission(Request $request, $submissionId)
    {
        $submission = Submission::with('team')->findOrFail($submissionId);
        $team = $submission->team;

        $request->validate([
            'action' => 'required|in:approved,rejected',
            'note' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            // Update status submission
            $submission->update([
                'status' => $request->action,
            ]);

            // Simpan hasil seleksi per stage
            SelectionResult::updateOrCreate(
                ['team_id' => $team->id, 'stage_id' => $submission->stage_id],
                [
                    'is_passed' => ($request->action === 'approved'),
                    'note' => $request->note,
                    'announced_at' => now(),
                ]
            );

            // Jika approved, update status tim sesuai stage
            if ($request->action === 'approved') {
                $stageName = strtolower($submission->stage->name);
                if (str_contains($stageName, 'first half')) {
                    $team->update(['selection_status' => 'semi_final']);
                } elseif (str_contains($stageName, 'semi')) {
                    $team->update(['selection_status' => 'final']);
                }
                // untuk final tidak ada status lanjutan
            }

            // Kirim pengumuman
            Announcement::create([
                'title' => 'Hasil Review Submission',
                'content' => "Submission Anda untuk tahap {$submission->stage->name} telah di-review. Status: " . ($request->action === 'approved' ? 'Lolos' : 'Tidak Lolos') . "\nCatatan: " . ($request->note ?? '-'),
                'target_team_id' => $team->id,
                'type' => 'team',
                'published_at' => now(),
            ]);

            DB::commit();
            return response()->json(['message' => 'Review berhasil disimpan']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal: ' . $e->getMessage()], 500);
        }
    }
}