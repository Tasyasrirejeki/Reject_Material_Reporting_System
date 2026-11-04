<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'department',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    // Relationships
    public function rejectReports()
    {
        return $this->hasMany(RejectReport::class, 'operator_id');
    }

    public function verifiedReports()
    {
        return $this->hasMany(RejectReport::class, 'line_leader_id');
    }

    // Role helpers
    public function isOperator(): bool
    {
        return $this->role === 'operator';
    }

    public function isLineLeader(): bool
    {
        return $this->role === 'line_leader';
    }

    public function isSupervisor(): bool
    {
        return $this->role === 'supervisor';
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function canAccessDashboard(): bool
    {
        return in_array($this->role, ['line_leader', 'supervisor']);
    }

    public function getRoleDisplayAttribute(): string
    {
        return match($this->role) {
            'operator' => 'Operator',
            'line_leader' => 'Line Leader',
            'supervisor' => 'Supervisor',
            'admin' => 'Admin',
            default => ucfirst($this->role),
        };
    }

    public function getDepartmentDisplayAttribute(): string
    {
        return match($this->department) {
            'lenscase' => 'Lenscase',
            'pouching' => 'Pouching',
            'all' => 'All Departments',
            default => ucfirst($this->department),
        };
    }
}
