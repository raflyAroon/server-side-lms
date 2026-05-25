<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SelectionResult extends Model
{
    protected $fillable = ['team_id', 'stage_id', 'is_passed', 'note', 'announced_at'];

    protected $casts = [
        'is_passed' => 'boolean',
        'announced_at' => 'datetime',
    ];

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function stage()
    {
        return $this->belongsTo(Stage::class);
    }
}