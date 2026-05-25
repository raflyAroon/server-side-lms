<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    protected $fillable = ['title', 'content', 'target_team_id', 'target_stage_id', 'type', 'published_at'];

    protected $casts = [
        'published_at' => 'datetime',
    ];

    public function targetTeam()
    {
        return $this->belongsTo(Team::class, 'target_team_id');
    }

    public function targetStage()
    {
        return $this->belongsTo(Stage::class, 'target_stage_id');
    }
}