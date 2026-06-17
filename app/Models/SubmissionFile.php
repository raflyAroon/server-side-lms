<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubmissionFile extends Model
{
    // app/Models/SubmissionFile.php
protected $fillable = [
    'submission_id', 'file_url', 'file_name', 'file_size',
    'file_type', 'external_url', 'mime_type', 'file_path',
    'is_verified', 'submission_category'
];

protected $casts = [
    'is_verified' => 'boolean',
];

    public function submission()
    {
        return $this->belongsTo(Submission::class);
    }
}