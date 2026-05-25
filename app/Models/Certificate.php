<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Certificate extends Model
{
    protected $fillable = ['team_id', 'event_id', 'certificate_url'];

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function event()
    {
        return $this->belongsTo(Event::class);
    }
}