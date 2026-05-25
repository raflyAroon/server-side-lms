<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScoreDetail extends Model
{
    protected $fillable = ['score_id', 'rubric_criteria_id', 'score_value'];

    protected $casts = [
        'score_value' => 'float',
    ];

    public function score()
    {
        return $this->belongsTo(Score::class);
    }

    public function rubricCriteria()
    {
        return $this->belongsTo(RubricCriteria::class, 'rubric_criteria_id');
    }
}