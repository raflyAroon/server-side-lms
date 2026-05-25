<?php

namespace App\Exports;

use App\Models\Submission;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class SubmissionsExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return Submission::with('team', 'stage')->get()->map(function ($sub) {
            return [
                $sub->team->team_name,
                $sub->stage->name,
                $sub->project_type,
                $sub->description,
                $sub->created_at,
            ];
        });
    }

    public function headings(): array
    {
        return ['Tim', 'Tahap', 'Jenis Project', 'Deskripsi', 'Tanggal Submit'];
    }
}