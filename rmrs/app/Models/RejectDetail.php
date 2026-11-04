<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RejectDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'report_id',
        'defect_type',
        'defect_category',
        'quantity',
        'description',
        'defect_location',
    ];

    protected $casts = [
        'quantity' => 'integer',
    ];

    // Relationship
    public function report()
    {
        return $this->belongsTo(RejectReport::class, 'report_id');
    }

    // Helpers
    public function getCategoryBadgeAttribute(): string
    {
        return match($this->defect_category) {
            'critical' => 'badge-critical',
            'major' => 'badge-major',
            'minor' => 'badge-minor',
            default => 'badge-minor',
        };
    }

    public function getCategoryDisplayAttribute(): string
    {
        return match($this->defect_category) {
            'critical' => 'Critical',
            'major' => 'Major',
            'minor' => 'Minor',
            default => 'Minor',
        };
    }
}
