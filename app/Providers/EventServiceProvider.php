<?php

namespace App\Providers;

use App\Models\Team;
use App\Models\Submission;
use App\Models\Score;
use App\Observers\TeamObserver;
use App\Observers\SubmissionObserver;
use App\Observers\ScoreObserver;
use Illuminate\Support\ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Team::observe(TeamObserver::class);
        Submission::observe(SubmissionObserver::class);
        Score::observe(ScoreObserver::class);
    }
}