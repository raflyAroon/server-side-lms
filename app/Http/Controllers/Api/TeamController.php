<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Team;
use App\Models\TeamMember;
use App\Models\User;
use App\Models\TeamHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
     * Tampilkan detail tim milik user yang sedang login (termasuk anggota)
     */
    public function show()
{
    $user = Auth::user();
    $team = Team::where('ketua_id', $user->id)
        ->with('members')
        ->firstOrFail();

    // Pemanggilan yang benar: berikan callback
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