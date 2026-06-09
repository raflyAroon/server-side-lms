<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Team extends Model
{
    use HasFactory;

    protected $fillable = [ 'team_name', 'institution', 'city', 'ketua_id', 'selection_status', 'selection_note', 'selection_processed_at' ];

    /**
     * The "booted" method of the model.
     * Auto-clear related cache when team is updated or deleted.
     */
    protected static function booted()
    {
        static::updated(function ($team) {
            // Clear cache untuk team itu sendiri
            Cache::forget("team:{$team->id}");

            // Clear cache dashboard peserta (ketua tim)
            if ($team->ketua_id) {
                Cache::forget("dashboard:peserta:{$team->ketua_id}");
            }

            // Clear cache announcements yang ditargetkan ke team ini
            Cache::forgetPattern("announcements:team:{$team->id}:*");
        });

        static::deleted(function ($team) {
            Cache::forget("team:{$team->id}");
            if ($team->ketua_id) {
                Cache::forget("dashboard:peserta:{$team->ketua_id}");
            }
            Cache::forgetPattern("announcements:team:{$team->id}:*");
        });
    }

    // ========== RELATIONS ==========
    public function ketua() { return $this->belongsTo(User::class, 'ketua_id'); }

    public function histories() { return $this->hasMany(TeamHistory::class); }

    public function submissions() { return $this->hasMany(Submission::class); }

    public function selectionResults() { return $this->hasMany(SelectionResult::class); }

    public function certificates() { return $this->hasMany(Certificate::class); }

    public function announcements() { return $this->hasMany(Announcement::class, 'target_team_id'); }
    
    public function members() { return $this->hasMany(TeamMember::class); }

    public function documents() { return $this->hasMany(TeamDocument::class); }
    
    public function getFullMembersAttribute() {
        $members = $this->members->keyBy('position');
        return [
            'ketua' => $members['ketua'] ?? null,
            'anggota1' => $members['anggota1'] ?? null,
            'anggota2' => $members['anggota2'] ?? null,
        ];
    }

    // Scope untuk admin
    public function scopePending($query) { return $query->where('selection_status', 'pending'); }  
    public function scopeApproved($query) { return $query->where('selection_status', 'approved'); }

    // ========== HELPER (OPTIONAL) ==========
    /** Clear all cache related to this team manually. **/
    public function clearCache(): void {
        Cache::forget("team:{$this->id}");
        if ($this->ketua_id) {
            Cache::forget("dashboard:peserta:{$this->ketua_id}");
        }
            Cache::forgetPattern("announcements:team:{$this->id}:*");
    }
}