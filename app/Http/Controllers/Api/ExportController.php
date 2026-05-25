<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Exports\TeamsExport;
use App\Exports\ScoresExport;
use App\Exports\SelectionResultsExport;
use App\Exports\SubmissionsExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;

class ExportController extends Controller
{
    public function teams()
    {
        $this->authorize('admin');
        return Excel::download(new TeamsExport, 'teams_'.date('Y-m-d').'.xlsx');
    }

    public function scores()
    {
        $this->authorize('admin');
        return Excel::download(new ScoresExport, 'scores_'.date('Y-m-d').'.xlsx');
    }

    public function selectionResults()
    {
        $this->authorize('admin');
        return Excel::download(new SelectionResultsExport, 'selection_results_'.date('Y-m-d').'.xlsx');
    }

    public function submissions()
    {
        $this->authorize('admin');
        return Excel::download(new SubmissionsExport, 'submissions_'.date('Y-m-d').'.xlsx');
    }
}