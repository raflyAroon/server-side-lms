<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeamHistory extends Model
{
    protected $fillable = ['team_id', 'snapshot_data', 'changed_by', 'changed_at'];

    protected $casts = [
        'snapshot_data' => 'array',
        'changed_at' => 'datetime',
    ];

    public function team()
    {
        return $this->belongsTo(Team::class);
    }
}