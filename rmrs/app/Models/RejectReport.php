<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class RejectReport extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'report_number',
        'operator_id',
        'line_leader_id',
        'department',
        'shift',
        'production_date',
        'product_name',
        'product_code',
        'total_production_qty',
        'total_reject_qty',
        'status',
        'operator_notes',
        'leader_notes',
        'submitted_at',
        'edited_at',
        'edit_count',
        'edit_history',
        'verified_at',
    ];

    protected $casts = [
        'production_date' => 'date',
        'submitted_at' => 'datetime',
        'edited_at' => 'datetime',
        'verified_at' => 'datetime',
        'total_production_qty' => 'integer',
        'total_reject_qty' => 'integer',
        'edit_count' => 'integer',
        'edit_history' => 'array',
    ];

    // Relationships
    public function operator()
    {
        return $this->belongsTo(User::class, 'operator_id');
    }

    public function lineLeader()
    {
        return $this->belongsTo(User::class, 'line_leader_id');
    }

    public function details()
    {
        return $this->hasMany(RejectDetail::class, 'report_id');
    }

    // Scopes
    /** Laporan yang sudah diedit operator dan menunggu verifikasi Line Leader. */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeVerified($query)
    {
        return $query->where('status', 'verified');
    }

    /** Data valid untuk statistik/grafik (laporan yang dikembalikan tidak dihitung). */
    public function scopeCounted($query)
    {
        return $query->whereIn('status', ['submitted', 'pending', 'verified']);
    }

    public function scopeByDepartment($query, $dept)
    {
        return $dept ? $query->where('department', $dept) : $query;
    }

    public function scopeByDateRange($query, $from, $to)
    {
        if ($from) $query->whereDate('production_date', '>=', $from);
        if ($to) $query->whereDate('production_date', '<=', $to);
        return $query;
    }

    // Helpers
    public function getStatusBadgeAttribute(): string
    {
        return 'badge-' . $this->status;
    }

    public function getStatusDisplayAttribute(): string
    {
        return match($this->status) {
            'submitted' => 'Terkirim',
            'pending' => 'Menunggu Verifikasi',
            'verified' => 'Terverifikasi',
            'rejected' => 'Dikembalikan',
            default => ucfirst($this->status),
        };
    }

    public function getIsEditedAttribute(): bool
    {
        return $this->edit_count > 0;
    }

    /** Verifikasi Line Leader hanya relevan untuk laporan yang diedit operator. */
    public function getNeedsVerificationAttribute(): bool
    {
        return $this->status === 'pending';
    }

    public function getDepartmentDisplayAttribute(): string
    {
        return match($this->department) {
            'lenscase' => 'Lenscase',
            'pouching' => 'Pouching',
            default => ucfirst($this->department),
        };
    }

    public const SHIFTS = [
        '1' => 'Shift 1 (07:00 - 19:00)',
        '2' => 'Shift 2 (19:00 - 07:00)',
    ];

    public function getShiftDisplayAttribute(): string
    {
        return self::SHIFTS[$this->shift] ?? ('Shift ' . $this->shift);
    }

    /**
     * Detect the active shift from a given time (default: now).
     * Shift 1: 07:00-18:59, Shift 2: 19:00-06:59.
     */
    public static function currentShift($time = null): string
    {
        $hour = ($time ?? now())->hour;
        return ($hour >= 7 && $hour < 19) ? '1' : '2';
    }

    /**
     * Returns edit_history with the Line Leader's decision attached to the latest edit entry.
     */
    public function historyWithDecision(string $status, ?string $notes, string $by): array
    {
        $history = $this->edit_history ?? [];
        if ($history) {
            $history[array_key_last($history)]['decision'] = [
                'status' => $status,
                'by' => $by,
                'at' => now()->toDateTimeString(),
                'notes' => $notes,
            ];
        }
        return $history;
    }

    // Auto-generate report number
    public static function generateReportNumber(string $department): string
    {
        $prefix = strtoupper(substr($department, 0, 2));
        $date = now()->format('Ymd');
        $count = self::whereDate('created_at', today())->where('department', $department)->count() + 1;
        return sprintf('RR-%s-%s-%04d', $prefix, $date, $count);
    }

    // Recalculate total reject qty from details
    public function recalculateTotals(): void
    {
        $this->update([
            'total_reject_qty' => $this->details()->sum('quantity'),
        ]);
    }

    /**
     * Snapshot of the editable header fields (used to diff operator edits).
     */
    public function editableSnapshot(): array
    {
        return [
            'Departemen' => $this->department_display,
            'Shift' => $this->shift_display,
            'Tanggal Produksi' => $this->production_date->format('d/m/Y'),
            'Jenis Material' => $this->product_name,
            'Batch Material' => $this->product_code ?: '-',
            'Qty Produksi' => number_format($this->total_production_qty) . ' pcs',
            'Catatan Operator' => $this->operator_notes ?: '-',
        ];
    }

    /**
     * Human readable list of defect rows (used to diff operator edits).
     */
    public function defectsSnapshot(): array
    {
        return $this->details()->orderBy('id')->get()->map(function ($d) {
            $line = $d->defect_type . ' [' . strtoupper($d->defect_category) . '] — ' . number_format($d->quantity) . ' pcs';
            if ($d->defect_location) $line .= ' · Lokasi: ' . $d->defect_location;
            if ($d->description) $line .= ' · ' . $d->description;
            return $line;
        })->all();
    }
}
