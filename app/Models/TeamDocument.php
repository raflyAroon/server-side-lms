<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeamDocument extends Model
{
    protected $fillable = [
        'team_id', 'type', 'file_name', 'file_path', 'file_url',
        'external_link', 'mime_type', 'file_size', 'is_verified'
    ];

    protected $casts = [
        'is_verified' => 'boolean',
    ];

    public function team()
    {
        return $this->belongsTo(Team::class);
    }
}