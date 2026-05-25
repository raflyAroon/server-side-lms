<?php

namespace App\Observers;

use App\Models\Score;
use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;

class ScoreObserver
{
    public function saved(Score $score): void
    {
        // Recalculate total score from details
        $total = 0;
        foreach ($score->details as $detail) {
            $weight = $detail->rubricCriteria->weight ?? 0;
            $maxScore = $detail->rubricCriteria->max_score ?? 100;
            // Normalisasi: score_value (0-100) * weight
            $total += ($detail->score_value * ($weight / 100));
        }
        // Pastikan total tidak melebihi 100 atau sesuai skala
        if (abs($total - $score->total_score) > 0.01) {
            $score->total_score = round($total, 2);
            $score->saveQuietly();
        }
    }

    public function created(Score $score): void
    {
        $this->logAudit('CREATE', $score);
    }

    public function updated(Score $score): void
    {
        $this->logAudit('UPDATE', $score);
    }

    public function deleted(Score $score): void
    {
        $this->logAudit('DELETE', $score);
    }

    private function logAudit(string $action, Score $score): void
    {
        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'entity_type' => 'score',
            'entity_id' => $score->id,
            'old_value_json' => $score->getOriginal(),
            'new_value_json' => $score->getChanges(),
            'ip_address' => request()->ip(),
        ]);
    }
}