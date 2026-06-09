<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Team;
use App\Models\TeamMember;
use App\Models\User;
use App\Models\TeamHistory;
use App\Http\Requests\Api\CompleteRegistrationRequest;
use App\Models\TeamDocument;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Traits\Cacheable;

class TeamController extends Controller
{
    use Cacheable;

    /**
     * Buat tim baru beserta 3 anggota (ketua, anggota1, anggota2)
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        // Pastikan user belum memiliki tim
        if ($user->teamAsKetua) {
            return response()->json(['message' => 'Anda sudah memiliki tim'], 400);
        }

        $validated = $request->validate([
            'team_name' => 'required|string|max:100|unique:teams,team_name',
            'institution' => 'nullable|string|max:200',
            'members' => 'required|array|min:3|max:3',
            'members.*.name' => 'required|string|max:100',
            'members.*.email' => 'required|email|distinct',
            'members.*.phone' => 'nullable|string|max:20',
            'members.*.position' => 'required|in:ketua,anggota1,anggota2',
        ]);

        // Validasi email ketua harus sama dengan email user yang login
        $ketuaData = collect($validated['members'])->firstWhere('position', 'ketua');
        if (!$ketuaData || $ketuaData['email'] !== $user->email) {
            return response()->json(['message' => 'Email ketua harus sama dengan email akun Anda'], 400);
        }

        DB::beginTransaction();
        try {
            $team = Team::create([
                'team_name' => $validated['team_name'],
                'institution' => $validated['institution'],
                'ketua_id' => $user->id,
            ]);

            foreach ($validated['members'] as $member) {
                TeamMember::create([
                    'team_id' => $team->id,
                    'name' => $member['name'],
                    'email' => $member['email'],
                    'phone' => $member['phone'] ?? null,
                    'position' => $member['position'],
                ]);
            }

            DB::commit();

            // Load relasi members untuk response
            $team->load('members');

            return response()->json([
                'team' => $team,
                'message' => 'Tim berhasil didaftarkan'
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Gagal membuat tim: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Pendaftaran tim lengkap (4 step: data tim, anggota, dokumen, konfirmasi)
     */
    public function completeRegistration(CompleteRegistrationRequest $request)
    {
        $user = auth()->user();

        // Pastikan user belum punya tim
        if ($user->teamAsKetua) {
            return response()->json(['message' => 'Anda sudah memiliki tim'], 400);
        }

        DB::beginTransaction();
        try {
            // 1. Buat Team
            $team = Team::create([
                'team_name' => $request->team_name,
                'institution' => $request->institution,
                'city' => $request->city,
                'ketua_id' => $user->id,
                'selection_status' => 'pending',
            ]);

            // 2. Buat TeamMembers (3 orang)
            foreach ($request->members as $memberData) {
                // Validasi email ketua harus sama dengan user login
                if ($memberData['position'] === 'ketua' && $memberData['email'] !== $user->email) {
                    throw new \Exception('Email ketua harus sama dengan akun Anda');
                }
                TeamMember::create([
                    'team_id' => $team->id,
                    'name' => $memberData['name'],
                    'email' => $memberData['email'],
                    'phone' => $memberData['phone'] ?? null,
                    'nim' => $memberData['nim'],
                    'faculty' => $memberData['faculty'],
                    'study_program' => $memberData['study_program'],
                    'position' => $memberData['position'],
                ]);
            }

            // 3. Upload & simpan dokumen
            $docMapping = [
                'hak_cipta' => 'hak_cipta',
                'komitmen' => 'komitmen',
                'rekomendasi' => 'rekomendasi',
                'summary_brief' => 'summary_brief',
                'ktm_ketua' => 'ktm_ketua',
                'ktm_anggota1' => 'ktm_anggota1',
                'ktm_anggota2' => 'ktm_anggota2',
            ];

            foreach ($docMapping as $field => $type) {
                if ($request->hasFile($field)) {
                    $file = $request->file($field);
                    $originalName = $file->getClientOriginalName();
                    $path = $file->store("team_documents/{$team->id}", 'public');
                    $url = Storage::url($path);

                    TeamDocument::create([
                        'team_id' => $team->id,
                        'type' => $type,
                        'file_name' => $originalName,
                        'file_path' => $path,
                        'file_url' => $url,
                        'mime_type' => $file->getMimeType(),
                        'file_size' => $file->getSize(),
                        'is_verified' => false,
                    ]);
                }
            }

            // 4. Simpan link video
            TeamDocument::create([
                'team_id' => $team->id,
                'type' => 'video_link',
                'external_link' => $request->video_link,
                'file_name' => 'Video Portofolio',
                'is_verified' => false,
            ]);

            DB::commit();

            // Bersihkan cache jika ada
            $this->forgetDashboard('peserta', $user->id);

            return response()->json([
                'message' => 'Pendaftaran tim berhasil! Silakan tunggu verifikasi admin.',
                'team_id' => $team->id,
                'selection_status' => $team->selection_status,
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Gagal mendaftarkan tim: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Tampilkan detail tim milik user yang sedang login (termasuk anggota)
     */
    public function show()
    {
        $user = Auth::user();
        $team = Team::where('ketua_id', $user->id)
            ->with('members')
            ->firstOrFail();

        $cachedTeam = $this->rememberTeam($team->id, function() use ($team) {
            return $team;
        });

        return response()->json($cachedTeam);
    }

    /**
     * Update tim (nama, institusi) dan anggota tim
     */
    public function update(Request $request)
    {
        $user = Auth::user();
        $team = Team::where('ketua_id', $user->id)->firstOrFail();

        $validated = $request->validate([
            'team_name' => 'sometimes|string|max:100|unique:teams,team_name,' . $team->id,
            'institution' => 'nullable|string|max:200',
            'members' => 'sometimes|array|min:3|max:3',
            'members.*.id' => 'nullable|exists:team_members,id',
            'members.*.name' => 'required|string|max:100',
            'members.*.email' => 'required|email|distinct',
            'members.*.phone' => 'nullable|string|max:20',
            'members.*.position' => 'required|in:ketua,anggota1,anggota2',
        ]);

        DB::beginTransaction();
        try {
            // Update data tim
            $team->update($validated);

            // Update atau buat anggota tim
            if (isset($validated['members'])) {
                $incomingMemberIds = [];
                foreach ($validated['members'] as $memberData) {
                    // Pastikan email ketua tidak diubah selain user yang login
                    if ($memberData['position'] === 'ketua' && $memberData['email'] !== $user->email) {
                        throw new \Exception('Email ketua tidak boleh diubah');
                    }

                    $member = TeamMember::updateOrCreate(
                        ['id' => $memberData['id'] ?? null, 'team_id' => $team->id],
                        [
                            'name' => $memberData['name'],
                            'email' => $memberData['email'],
                            'phone' => $memberData['phone'] ?? null,
                            'position' => $memberData['position'],
                        ]
                    );
                    $incomingMemberIds[] = $member->id;
                }

                // Hapus anggota yang tidak ada di list request
                $team->members()->whereNotIn('id', $incomingMemberIds)->delete();
            }

            DB::commit();

            // Hapus cache
            $this->forgetTeam($team->id);
            $this->forgetDashboard('peserta', $user->id);
            $this->forgetAnnouncementsByTeam($team->id);

            // Load ulang relasi members
            $team->load('members');

            return response()->json([
                'team' => $team,
                'message' => 'Tim berhasil diperbarui'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Gagal update tim: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Riwayat perubahan data tim (dari team_histories)
     */
    public function history()
    {
        $user = Auth::user();
        $team = Team::where('ketua_id', $user->id)->firstOrFail();
        $histories = $team->histories()->orderBy('changed_at', 'desc')->get();
        return response()->json($histories);
    }

    /**
     * Restore data tim dari snapshot history tertentu
     */
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

        // Hapus cache
        $this->forgetTeam($team->id);
        $this->forgetDashboard('peserta', $user->id);
        $this->forgetAnnouncementsByTeam($team->id);

        return response()->json([
            'message' => 'Tim berhasil dikembalikan dari riwayat',
            'team' => $team
        ]);
    }
}