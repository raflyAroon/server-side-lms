<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Score extends Model
{
    protected $fillable = ['submission_id', 'juri_id', 'total_score', 'feedback'];

    protected $casts = [
        'total_score' => 'float',
    ];

    public function submission()
    {
        return $this->belongsTo(Submission::class);
    }

    public function juri()
    {
        return $this->belongsTo(User::class, 'juri_id');
    }

    public function details()
    {
        return $this->hasMany(ScoreDetail::class);
    }
}