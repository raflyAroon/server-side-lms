<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeamMember extends Model {
    protected $fillable = [ 'team_id', 'name', 'email', 'phone', 'position', 'nim', 'faculty', 'study_program', 'shirt_size' ];

    protected $casts = [
        'shirt_size' => 'string',
    ];

    public function team() { return $this->belongsTo(Team::class); }
    // app/Models/TeamMember.php
    public function user() { return $this->belongsTo(User::class, 'email', 'email'); }// asumsi email sebagai foreign key
}