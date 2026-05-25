<?php

namespace App\Exports;

use App\Models\SelectionResult;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class SelectionResultsExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return SelectionResult::with('team', 'stage')->get()->map(function ($sr) {
            return [
                $sr->team->team_name,
                $sr->stage->name,
                $sr->is_passed ? 'Lolos' : 'Tidak Lolos',
                $sr->note,
                $sr->announced_at,
            ];
        });
    }

    public function headings(): array
    {
        return ['Tim', 'Tahap', 'Status', 'Catatan', 'Diumumkan'];
    }
}