<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Submission extends Model
{
    protected $fillable = ['team_id', 'stage_id', 'project_type', 'description'];

    protected $casts = [
        'project_type' => 'string',
    ];

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function stage()
    {
        return $this->belongsTo(Stage::class);
    }

    public function files()
    {
        return $this->hasMany(SubmissionFile::class);
    }

    public function scores()
    {
        return $this->hasMany(Score::class);
    }
}