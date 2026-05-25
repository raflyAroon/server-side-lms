<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    protected $fillable = ['user_id', 'action', 'entity_type', 'entity_id', 'old_value_json', 'new_value_json', 'ip_address'];

    protected $casts = [
        'old_value_json' => 'array',
        'new_value_json' => 'array',
        'ip_address' => 'string',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}