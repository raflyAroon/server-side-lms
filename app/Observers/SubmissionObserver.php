<?php

namespace App\Observers;

use App\Models\Submission;
use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;

class SubmissionObserver
{
    public function created(Submission $submission): void
    {
        $this->log('CREATE', $submission, null, $submission->toArray());
    }

    public function updated(Submission $submission): void
    {
        $this->log('UPDATE', $submission, $submission->getOriginal(), $submission->getChanges());
    }

    public function deleted(Submission $submission): void
    {
        $this->log('DELETE', $submission, $submission->toArray(), null);
    }

    private function log(string $action, Submission $submission, ?array $old, ?array $new): void
    {
        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'entity_type' => 'submission',
            'entity_id' => $submission->id,
            'old_value_json' => $old,
            'new_value_json' => $new,
            'ip_address' => request()->ip(),
        ]);
    }
}