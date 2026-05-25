<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'users';
    protected $primaryKey = 'id';
    protected $fillable = ['name', 'email', 'password_hash', 'role'];
    protected $hidden = ['password_hash'];
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function getAuthPassword()
    {
        return $this->password_hash;
    }

    // Relasi
    public function teamAsKetua()
    {
        return $this->hasOne(Team::class, 'ketua_id');
    }

    public function scores()
    {
        return $this->hasMany(Score::class, 'juri_id');
    }

    public function otps()
    {
        return $this->hasMany(Otp::class);
    }

    public function auditLogs()
    {
        return $this->hasMany(AuditLog::class);
    }
}