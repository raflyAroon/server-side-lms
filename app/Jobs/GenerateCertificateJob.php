<?php

namespace App\Jobs;

use App\Models\Certificate;
use App\Models\Team;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Storage;

class GenerateCertificateJob implements ShouldQueue
{
    use Queueable;

    public function __construct(public Team $team, public int $eventId)
    {}

    public function handle(): void
    {
        $certificate = Certificate::updateOrCreate(
            ['team_id' => $this->team->id, 'event_id' => $this->eventId],
            []
        );

        $pdf = Pdf::loadView('pdf.certificate', [
            'teamName' => $this->team->team_name,
            'eventName' => $this->team->certificates->first()?->event->name ?? 'Lomba Coding MPR RI 2026'
        ]);

        $path = "certificates/team_{$this->team->id}_event_{$this->eventId}.pdf";
        Storage::disk('public')->put($path, $pdf->output());

        $certificate->update(['certificate_url' => Storage::url($path)]);
    }
}