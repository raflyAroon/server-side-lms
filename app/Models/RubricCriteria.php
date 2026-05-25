<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RubricCriteria extends Model
{
    protected $fillable = ['rubric_id', 'criterion_name', 'max_score', 'weight'];

    protected $casts = [
        'max_score' => 'integer',
        'weight' => 'float',
    ];

    public function rubric()
    {
        return $this->belongsTo(Rubric::class);
    }

    public function scoreDetails()
    {
        return $this->hasMany(ScoreDetail::class);
    }
}