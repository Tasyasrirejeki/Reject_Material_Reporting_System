@extends('layouts.app')
@section('title', 'Dashboard Line Leader')
@section('page-title', 'Dashboard Line Leader')

@push('styles')
<style>
    .pending-card {
        background: var(--bg-card);
        border: 1px solid rgba(245,158,11,0.2);
        border-radius: var(--radius);
        padding: 16px;
        display: flex;
        align-items: center;
        gap: 16px;
        transition: all 0.2s;
    }
    .pending-card:hover { border-color: var(--warning); transform: translateX(4px); }
    .pending-info { flex: 1; min-width: 0; }
    .pending-num { font-family: 'JetBrains Mono',monospace; font-size: 12px; color: var(--primary); font-weight: 700; }
    .pending-product { font-size: 14px; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .pending-meta { font-size: 11px; color: var(--text-secondary); margin-top: 2px; }
</style>
@endpush

@section('content')
{{-- Stats --}}
<div class="stat-grid fade-in">
    <div class="stat-card yellow">
        <div class="stat-icon"><i class="fas fa-clock"></i></div>
        <div class="stat-value">{{ $stats['pending_count'] }}</div>
        <div class="stat-label">Menunggu Verifikasi</div>
        <div class="stat-sub">Perlu ditindak sekarang</div>
    </div>
    <div class="stat-card green">
        <div class="stat-icon"><i class="fas fa-check-double"></i></div>
        <div class="stat-value">{{ $stats['verified_today'] }}</div>
        <div class="stat-label">Diverifikasi Hari Ini</div>
    </div>
    <div class="stat-card purple">
        <div class="stat-icon"><i class="fas fa-file-alt"></i></div>
        <div class="stat-value">{{ $stats['total_today'] }}</div>
        <div class="stat-label">Total Laporan Hari Ini</div>
    </div>
    <div class="stat-card red">
        <div class="stat-icon"><i class="fas fa-exclamation-triangle"></i></div>
        <div class="stat-value">{{ number_format($stats['total_reject_today']) }}</div>
        <div class="stat-label">Total Reject Hari Ini</div>
        <div class="stat-sub">pcs</div>
    </div>
</div>

<div class="grid-2 fade-in fade-in-2">
    {{-- Pending verification --}}
    <div class="card">
        <div class="card-header">
            <div class="card-title"><i class="fas fa-clock" style="color:var(--warning)"></i> Menunggu Verifikasi</div>
            <a href="{{ route('leader.reports', ['status' => 'pending']) }}" class="btn btn-outline btn-sm">Lihat Semua</a>
        </div>
        <div class="card-body">
            @if($pendingReports->isEmpty())
                <div class="empty-state" style="padding:30px 0;">
                    <i class="fas fa-check-circle" style="color:var(--success)"></i>
                    <h3>Semua Selesai!</h3>
                    <p>Tidak ada laporan yang menunggu verifikasi.</p>
                </div>
            @else
                <div style="display:flex;flex-direction:column;gap:10px;">
                    @foreach($pendingReports->take(6) as $report)
                    <a href="{{ route('leader.report.show', $report) }}" style="text-decoration:none">
                        <div class="pending-card">
                            <div style="width:40px;height:40px;border-radius:10px;background:rgba(245,158,11,0.1);display:flex;align-items:center;justify-content:center;color:var(--warning);font-size:16px;flex-shrink:0;">
                                <i class="fas fa-file-alt"></i>
                            </div>
                            <div class="pending-info">
                                <div class="pending-num">{{ $report->report_number }}</div>
                                <div class="pending-product">{{ $report->product_name }}</div>
                                <div class="pending-meta">
                                    <span class="badge badge-{{ $report->department }}" style="font-size:10px;padding:2px 7px;">{{ $report->department_display }}</span>
                                    · {{ $report->shift_display }}
                                    · {{ $report->operator->name }}
                                    · <b style="color:var(--warning)">{{ number_format($report->total_reject_qty) }} pcs</b>
                                </div>
                            </div>
                            <i class="fas fa-chevron-right" style="color:var(--text-secondary);font-size:12px;"></i>
                        </div>
                    </a>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    {{-- Charts --}}
    <div style="display:flex;flex-direction:column;gap:16px;">
        <div class="card">
            <div class="card-header">
                <div class="card-title"><i class="fas fa-chart-bar"></i> Reject per Departemen (Bulan Ini)</div>
            </div>
            <div class="card-body">
                <div class="chart-container" style="height:180px;">
                    <canvas id="deptChart"></canvas>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <div class="card-title"><i class="fas fa-chart-line"></i> Tren 7 Hari Terakhir</div>
            </div>
            <div class="card-body">
                <div class="chart-container" style="height:160px;">
                    <canvas id="trendChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
Chart.defaults.color = '#8888AA';
Chart.defaults.borderColor = 'rgba(108,99,255,0.1)';

// Dept chart
new Chart(document.getElementById('deptChart'), {
    type: 'doughnut',
    data: {
        labels: {!! json_encode($deptChart->keys()->map(fn($k) => ucfirst($k))->toArray()) !!},
        datasets: [{
            data: {!! json_encode($deptChart->values()->toArray()) !!},
            backgroundColor: ['rgba(108,99,255,0.8)', 'rgba(0,212,170,0.8)'],
            borderColor: ['#6C63FF', '#00D4AA'],
            borderWidth: 2,
        }]
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        plugins: {
            legend: { position: 'right', labels: { padding: 16, font: { size: 12 } } }
        }
    }
});

// Trend chart
const trendData = @json($trend);
const labels = Object.keys(trendData).map(d => {
    const dt = new Date(d);
    return dt.toLocaleDateString('id-ID', {day:'2-digit', month:'short'});
});

new Chart(document.getElementById('trendChart'), {
    type: 'line',
    data: {
        labels,
        datasets: [{
            label: 'Total Reject',
            data: Object.values(trendData),
            borderColor: '#6C63FF',
            backgroundColor: 'rgba(108,99,255,0.1)',
            fill: true,
            tension: 0.4,
            pointBackgroundColor: '#6C63FF',
            pointRadius: 4,
        }]
    },
    options: {
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
            x: { grid: { color: 'rgba(108,99,255,0.06)' } },
            y: { grid: { color: 'rgba(108,99,255,0.06)' }, beginAtZero: true }
        }
    }
});
</script>
@endpush
