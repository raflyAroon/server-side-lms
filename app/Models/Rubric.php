<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rubric extends Model
{
    protected $fillable = ['stage_id', 'name', 'description'];

    public function stage()
    {
        return $this->belongsTo(Stage::class);
    }

    public function criteria()
    {
        return $this->hasMany(RubricCriteria::class);
    }
}