<?php

namespace App\Exports;

use App\Models\Score;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ScoresExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return Score::with('submission.team', 'juri')->get()->map(function ($score) {
            return [
                $score->submission->team->team_name,
                $score->submission->stage->name,
                $score->juri->name,
                $score->total_score,
                $score->feedback,
                $score->created_at,
            ];
        });
    }

    public function headings(): array
    {
        return ['Tim', 'Tahap', 'Juri', 'Total Skor', 'Feedback', 'Tanggal Penilaian'];
    }
}