<?php

namespace App\Jobs;

use App\Exports\TeamsExport;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;
use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;

class ExportDataJob implements ShouldQueue
{
    use Queueable;

    public function __construct(public string $type, public ?int $userId = null)
    {}

    public function handle(): void
    {
        $fileName = "exports/{$this->type}_" . now()->timestamp . ".xlsx";
        $disk = 'public';
        switch ($this->type) {
            case 'teams':
                Excel::store(new TeamsExport, $fileName, $disk);
                break;
            default:
                throw new \Exception("Export type not supported");
        }
        // Optionally store reference in database for download later
        AuditLog::create([
            'user_id' => $this->userId,
            'action' => 'EXPORT',
            'entity_type' => $this->type,
            'entity_id' => 0,
            'new_value_json' => ['file' => $fileName],
            'ip_address' => request()->ip(),
        ]);
    }
}