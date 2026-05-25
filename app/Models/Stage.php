<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Stage extends Model
{
    protected $fillable = ['event_id', 'name', 'stage_order', 'is_active'];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function submissions()
    {
        return $this->hasMany(Submission::class);
    }

    public function rubrics()
    {
        return $this->hasMany(Rubric::class);
    }

    public function selectionResults()
    {
        return $this->hasMany(SelectionResult::class);
    }

    public function announcements()
    {
        return $this->hasMany(Announcement::class, 'target_stage_id');
    }
}