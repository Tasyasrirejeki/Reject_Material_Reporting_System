@extends('layouts.app')
@section('title', 'Detail Laporan ' . $report->report_number)
@section('page-title', 'Detail Laporan')

@section('content')
<div style="max-width:900px;">
    <div style="display:flex;align-items:center;gap:16px;margin-bottom:20px;">
        <a href="{{ route('operator.reports.index') }}" class="btn btn-outline btn-sm"><i class="fas fa-arrow-left"></i> Kembali</a>
        <div>
            <div style="font-size:18px;font-weight:800;">{{ $report->report_number }}</div>
            <div style="font-size:12px;color:var(--text-secondary)">Dibuat: {{ $report->created_at->format('d F Y H:i') }}</div>
        </div>
        <div style="margin-left:auto;">
            <span class="badge badge-{{ $report->status }}" style="font-size:13px;padding:6px 16px;">
                {{ $report->status_display }}
            </span>
        </div>
    </div>

    {{-- Info produksi --}}
    <div class="card fade-in" style="margin-bottom:16px;">
        <div class="card-header">
            <div class="card-title"><i class="fas fa-industry"></i> Informasi Produksi</div>
        </div>
        <div class="card-body">
            <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px;">
                @php
                    $fields = [
                        ['Departemen', $report->department_display],
                        ['Shift', $report->shift_display],
                        ['Tanggal Produksi', $report->production_date->format('d F Y')],
                        ['Nama Produk', $report->product_name],
                        ['Kode Produk', $report->product_code ?: '-'],
                        ['Lot Number', $report->lot_number ?: '-'],
                        ['No. Mesin', $report->machine_number ?: '-'],
                        ['Qty Produksi', number_format($report->total_production_qty) . ' pcs'],
                        ['Operator', $report->operator->name],
                        ['Waktu Submit', ($report->submitted_at ?? $report->created_at)->format('d/m/Y H:i:s')],
                    ];
                @endphp
                @foreach($fields as [$label, $val])
                <div>
                    <div style="font-size:11px;font-weight:700;color:var(--text-secondary);text-transform:uppercase;letter-spacing:0.5px;margin-bottom:4px;">{{ $label }}</div>
                    <div style="font-size:14px;font-weight:600;">{{ $val }}</div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Summary reject --}}
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-bottom:16px;" class="fade-in fade-in-2">
        <div class="stat-card yellow">
            <div class="stat-icon"><i class="fas fa-exclamation-triangle"></i></div>
            <div class="stat-value">{{ number_format($report->total_reject_qty) }}</div>
            <div class="stat-label">Total Qty Reject</div>
        </div>
        <div class="stat-card {{ $report->reject_percentage > 5 ? 'red' : ($report->reject_percentage > 2 ? 'yellow' : 'green') }}">
            <div class="stat-icon"><i class="fas fa-percent"></i></div>
            <div class="stat-value">{{ $report->reject_percentage }}%</div>
            <div class="stat-label">Persentase Reject</div>
        </div>
        <div class="stat-card purple">
            <div class="stat-icon"><i class="fas fa-list"></i></div>
            <div class="stat-value">{{ $report->details->count() }}</div>
            <div class="stat-label">Jenis Defect</div>
        </div>
    </div>

    {{-- Detail defect --}}
    <div class="card fade-in fade-in-3" style="margin-bottom:16px;">
        <div class="card-header">
            <div class="card-title"><i class="fas fa-bug"></i> Detail Jenis Defect</div>
        </div>
        <div class="table-wrapper" style="border:none;border-radius:0;">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Jenis Defect</th>
                        <th>Kategori</th>
                        <th>Qty (pcs)</th>
                        <th>Lokasi</th>
                        <th>Deskripsi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($report->details as $i => $detail)
                    <tr>
                        <td style="color:var(--text-secondary)">{{ $i + 1 }}</td>
                        <td><b>{{ $detail->defect_type }}</b></td>
                        <td><span class="badge badge-{{ $detail->defect_category }}">{{ $detail->category_display }}</span></td>
                        <td><b style="color:var(--warning);font-family:JetBrains Mono,monospace">{{ number_format($detail->quantity) }}</b></td>
                        <td style="color:var(--text-secondary)">{{ $detail->defect_location ?: '-' }}</td>
                        <td style="color:var(--text-secondary)">{{ $detail->description ?: '-' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Catatan --}}
    <div class="card fade-in fade-in-4">
        <div class="card-header"><div class="card-title"><i class="fas fa-sticky-note"></i> Catatan</div></div>
        <div class="card-body">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">
                <div>
                    <div style="font-size:11px;font-weight:700;color:var(--text-secondary);text-transform:uppercase;letter-spacing:0.5px;margin-bottom:8px;">Catatan Operator</div>
                    <div style="background:var(--bg-elevated);border-radius:8px;padding:12px;font-size:13px;color:var(--text-primary);">
                        {{ $report->operator_notes ?: 'Tidak ada catatan.' }}
                    </div>
                </div>
                <div>
                    <div style="font-size:11px;font-weight:700;color:var(--text-secondary);text-transform:uppercase;letter-spacing:0.5px;margin-bottom:8px;">
                        Catatan Line Leader
                        @if($report->verified_at)
                            <span style="color:var(--text-muted,#555577);font-weight:400;margin-left:8px;">{{ $report->verified_at->format('d/m/Y H:i') }}</span>
                        @endif
                    </div>
                    <div style="background:var(--bg-elevated);border-radius:8px;padding:12px;font-size:13px;color:{{ $report->status === 'rejected' ? 'var(--danger)' : 'var(--text-primary)' }};">
                        {{ $report->leader_notes ?: 'Belum ada catatan.' }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
