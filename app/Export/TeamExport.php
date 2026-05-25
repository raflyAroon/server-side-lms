<?php

namespace App\Exports;

use App\Models\Team;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class TeamsExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return Team::with('ketua')->get()->map(function ($team) {
            return [
                $team->team_name,
                $team->institution,
                $team->ketua->name,
                $team->ketua->email,
                $team->created_at,
            ];
        });
    }

    public function headings(): array
    {
        return ['Nama Tim', 'Institusi', 'Ketua Tim', 'Email Ketua', 'Tanggal Daftar'];
    }
}