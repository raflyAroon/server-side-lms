<?php

namespace App\Observers;

use App\Models\Team;
use App\Models\TeamHistory;
use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;

class TeamObserver
{
    public function created(Team $team): void
    {
        $this->logAudit('CREATE', $team, null, $team->toArray());
        $this->saveHistory($team);
    }

    public function updated(Team $team): void
    {
        $old = $team->getOriginal();
        $new = $team->getChanges();
        $this->logAudit('UPDATE', $team, $old, $new);
        $this->saveHistory($team);
    }

    public function deleted(Team $team): void
    {
        $this->logAudit('DELETE', $team, $team->toArray(), null);
    }

    private function logAudit(string $action, Team $team, ?array $old, ?array $new): void
    {
        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'entity_type' => 'team',
            'entity_id' => $team->id,
            'old_value_json' => $old,
            'new_value_json' => $new,
            'ip_address' => request()->ip(),
        ]);
    }

    private function saveHistory(Team $team): void
    {
        TeamHistory::create([
            'team_id' => $team->id,
            'snapshot_data' => $team->toArray(),
            'changed_by' => Auth::user()?->email ?? 'system',
            'changed_at' => now(),
        ]);
    }
}