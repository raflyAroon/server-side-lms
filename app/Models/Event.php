<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    protected $fillable = ['name', 'description', 'start_date', 'end_date'];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function stages()
    {
        return $this->hasMany(Stage::class);
    }

    public function schedules()
    {
        return $this->hasMany(Schedule::class);
    }

    public function certificates()
    {
        return $this->hasMany(Certificate::class);
    }
}