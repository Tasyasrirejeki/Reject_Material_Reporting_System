@extends('layouts.app')
@section('title', 'Dashboard Supervisor')
@section('page-title', 'Dashboard Supervisor')

@push('styles')
<style>
    .defect-bar { background: var(--bg-elevated); border-radius: 4px; height: 8px; overflow: hidden; margin-top: 4px; }
    .defect-bar-fill { height: 100%; background: linear-gradient(90deg, var(--primary), var(--secondary)); border-radius: 4px; transition: width 1s ease; }
</style>
@endpush

@section('content')
{{-- KPI Stats --}}
<div class="stat-grid fade-in">
    <div class="stat-card purple">
        <div class="stat-icon"><i class="fas fa-file-alt"></i></div>
        <div class="stat-value">{{ number_format($stats['total_reports']) }}</div>
        <div class="stat-label">Total Laporan</div>
        <div class="stat-sub">Sepanjang waktu</div>
    </div>
    <div class="stat-card yellow">
        <div class="stat-icon"><i class="fas fa-clock"></i></div>
        <div class="stat-value">{{ $stats['pending'] }}</div>
        <div class="stat-label">Pending Verifikasi</div>
    </div>
    <div class="stat-card green">
        <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
        <div class="stat-value">{{ $stats['verified'] }}</div>
        <div class="stat-label">Terverifikasi</div>
    </div>
    <div class="stat-card red">
        <div class="stat-icon"><i class="fas fa-ban"></i></div>
        <div class="stat-value">{{ $stats['rejected'] }}</div>
        <div class="stat-label">Ditolak/Dikembalikan</div>
    </div>
    <div class="stat-card teal">
        <div class="stat-icon"><i class="fas fa-exclamation-triangle"></i></div>
        <div class="stat-value">{{ number_format($stats['total_reject_this_month']) }}</div>
        <div class="stat-label">Total Reject Bulan Ini</div>
        <div class="stat-sub">pcs (terverifikasi)</div>
    </div>
    <div class="stat-card purple">
        <div class="stat-icon"><i class="fas fa-users"></i></div>
        <div class="stat-value">{{ $stats['total_operators'] }}</div>
        <div class="stat-label">Operator Aktif</div>
    </div>
</div>

{{-- Charts row --}}
<div class="grid-2 fade-in fade-in-2" style="margin-bottom:20px;">
    {{-- Monthly trend --}}
    <div class="card">
        <div class="card-header">
            <div class="card-title"><i class="fas fa-chart-line"></i> Tren Reject 6 Bulan Terakhir</div>
        </div>
        <div class="card-body">
            <div class="chart-container">
                <canvas id="monthlyChart"></canvas>
            </div>
        </div>
    </div>

    {{-- Daily trend --}}
    <div class="card">
        <div class="card-header">
            <div class="card-title"><i class="fas fa-chart-area"></i> Tren Harian 14 Hari Terakhir</div>
        </div>
        <div class="card-body">
            <div class="chart-container">
                <canvas id="dailyChart"></canvas>
            </div>
        </div>
    </div>
</div>

{{-- Bottom row --}}
<div class="grid-2 fade-in fade-in-3" style="margin-bottom:20px;">
    {{-- Dept Stats --}}
    <div class="card">
        <div class="card-header">
            <div class="card-title"><i class="fas fa-chart-pie"></i> Statistik per Departemen (Bulan Ini)</div>
        </div>
        <div class="card-body">
            @if($deptStats->isEmpty())
                <div class="empty-state" style="padding:30px 0;"><i class="fas fa-database"></i><h3>Belum ada data</h3></div>
            @else
                <div style="display:flex;flex-direction:column;gap:16px;">
                    @foreach($deptStats as $dept)
                    <div>
                        <div style="display:flex;justify-content:space-between;margin-bottom:6px;">
                            <div>
                                <span class="badge badge-{{ $dept->department }}">{{ ucfirst($dept->department) }}</span>
                                <span style="font-size:13px;color:var(--text-secondary);margin-left:8px;">{{ $dept->total_reports }} laporan</span>
                            </div>
                            <div style="text-align:right;">
                                <span style="font-weight:700;color:var(--warning);font-family:JetBrains Mono,monospace">{{ number_format($dept->total_qty) }} pcs</span>
                                <span style="font-size:11px;color:var(--text-secondary);margin-left:6px;">{{ number_format($dept->avg_pct, 2) }}% avg</span>
                            </div>
                        </div>
                        @php $maxQty = $deptStats->max('total_qty'); $pct = $maxQty > 0 ? ($dept->total_qty / $maxQty) * 100 : 0; @endphp
                        <div class="defect-bar">
                            <div class="defect-bar-fill" style="width: {{ $pct }}%; background: {{ $dept->department === 'lenscase' ? 'linear-gradient(90deg, #6C63FF, #9C96FF)' : 'linear-gradient(90deg, #00D4AA, #00F0C0)' }};"></div>
                        </div>
                    </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    {{-- Top Defects --}}
    <div class="card">
        <div class="card-header">
            <div class="card-title"><i class="fas fa-bug"></i> Top Jenis Defect (Bulan Ini)</div>
        </div>
        <div class="card-body">
            @if($topDefects->isEmpty())
                <div class="empty-state" style="padding:30px 0;"><i class="fas fa-database"></i><h3>Belum ada data</h3></div>
            @else
                @php $maxDefect = $topDefects->max('total'); @endphp
                <div style="display:flex;flex-direction:column;gap:12px;">
                    @foreach($topDefects as $defect)
                    <div>
                        <div style="display:flex;justify-content:space-between;margin-bottom:4px;">
                            <div style="display:flex;align-items:center;gap:8px;">
                                <span class="badge badge-{{ $defect->defect_category }}" style="font-size:9px;padding:2px 6px;">{{ strtoupper($defect->defect_category) }}</span>
                                <span style="font-size:12px;font-weight:600;">{{ $defect->defect_type }}</span>
                            </div>
                            <span style="font-weight:700;font-family:JetBrains Mono,monospace;font-size:12px;color:var(--warning)">{{ number_format($defect->total) }}</span>
                        </div>
                        @php $w = $maxDefect > 0 ? ($defect->total / $maxDefect) * 100 : 0; @endphp
                        <div class="defect-bar"><div class="defect-bar-fill" style="width:{{ $w }}%"></div></div>
                    </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>

{{-- Recent Reports --}}
<div class="card fade-in fade-in-4">
    <div class="card-header">
        <div class="card-title"><i class="fas fa-history"></i> Laporan Terbaru</div>
        <div style="display:flex;gap:10px;">
            <a href="{{ route('supervisor.reports') }}" class="btn btn-outline btn-sm">Lihat Semua</a>
            <a href="{{ route('supervisor.export') }}" class="btn btn-export btn-sm"><i class="fas fa-file-excel"></i> Export</a>
        </div>
    </div>
    <div class="table-wrapper" style="border:none;border-radius:0;">
        <table>
            <thead><tr><th>No. Laporan</th><th>Tgl Produksi</th><th>Dept</th><th>Produk</th><th>Operator</th><th>Qty Reject</th><th>% Reject</th><th>Status</th></tr></thead>
            <tbody>
                @foreach($recentReports as $r)
                <tr>
                    <td><span class="report-number">{{ $r->report_number }}</span></td>
                    <td>{{ $r->production_date->format('d/m/Y') }}</td>
                    <td><span class="badge badge-{{ $r->department }}">{{ $r->department_display }}</span></td>
                    <td>{{ Str::limit($r->product_name, 22) }}</td>
                    <td style="color:var(--text-secondary)">{{ $r->operator->name }}</td>
                    <td><b style="color:var(--warning)">{{ number_format($r->total_reject_qty) }}</b></td>
                    <td><span style="color:{{ $r->reject_percentage>5?'var(--danger)':($r->reject_percentage>2?'var(--warning)':'var(--success)') }};font-weight:700">{{ $r->reject_percentage }}%</span></td>
                    <td><span class="badge badge-{{ $r->status }}">{{ $r->status_display }}</span></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
Chart.defaults.color = '#8888AA';
Chart.defaults.borderColor = 'rgba(108,99,255,0.08)';

const monthlyData = @json($monthlyTrend);
const monthLabels = monthlyData.map(d => {
    const months = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
    return months[d.month - 1] + ' ' + d.year;
});

new Chart(document.getElementById('monthlyChart'), {
    type: 'bar',
    data: {
        labels: monthLabels,
        datasets: [{
            label: 'Total Reject (pcs)',
            data: monthlyData.map(d => d.total),
            backgroundColor: 'rgba(108,99,255,0.7)',
            borderColor: '#6C63FF',
            borderWidth: 1,
            borderRadius: 6,
        }, {
            label: 'Jumlah Laporan',
            data: monthlyData.map(d => d.reports),
            backgroundColor: 'rgba(0,212,170,0.5)',
            borderColor: '#00D4AA',
            borderWidth: 1,
            borderRadius: 6,
            yAxisID: 'y2',
        }]
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { position: 'top', labels: { padding: 16, font: { size: 11 } } } },
        scales: {
            x: { grid: { color: 'rgba(108,99,255,0.05)' } },
            y: { grid: { color: 'rgba(108,99,255,0.05)' }, beginAtZero: true, title: { display: true, text: 'Qty Reject' } },
            y2: { position: 'right', grid: { display: false }, beginAtZero: true, title: { display: true, text: 'Laporan' } },
        }
    }
});

const dailyData = @json($dailyTrend);
const dailyLabels = Object.keys(dailyData).map(d => {
    const dt = new Date(d);
    return dt.toLocaleDateString('id-ID', {day:'2-digit', month:'short'});
});

new Chart(document.getElementById('dailyChart'), {
    type: 'line',
    data: {
        labels: dailyLabels,
        datasets: [{
            label: 'Reject Harian',
            data: Object.values(dailyData),
            borderColor: '#00D4AA',
            backgroundColor: 'rgba(0,212,170,0.1)',
            fill: true,
            tension: 0.4,
            pointBackgroundColor: '#00D4AA',
            pointRadius: 4,
            pointHoverRadius: 7,
        }]
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
            x: { grid: { color: 'rgba(108,99,255,0.05)' } },
            y: { grid: { color: 'rgba(108,99,255,0.05)' }, beginAtZero: true }
        }
    }
});
</script>
@endpush
