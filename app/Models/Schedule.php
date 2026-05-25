<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    protected $fillable = ['event_id', 'date_time', 'description', 'location'];

    protected $casts = [
        'date_time' => 'datetime',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }
}