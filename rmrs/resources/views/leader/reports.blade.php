@extends('layouts.app')
@section('title', 'Semua Laporan')
@section('page-title', 'Monitoring Laporan')

@section('content')
<div class="card fade-in">
    <div class="card-header">
        <div class="card-title"><i class="fas fa-file-alt"></i> Semua Laporan Reject</div>
        <a href="{{ route('leader.export', request()->query()) }}" class="btn btn-export btn-sm">
            <i class="fas fa-file-excel"></i> Export Excel
        </a>
    </div>

    <form method="GET" style="padding:16px 20px;border-bottom:1px solid var(--border);">
        <div style="display:flex;flex-wrap:wrap;gap:12px;align-items:flex-end;">
            <div class="filter-group">
                <label class="filter-label">Cari</label>
                <input type="text" name="search" class="filter-control" value="{{ request('search') }}" placeholder="No. laporan / produk / lot...">
            </div>
            <div class="filter-group" style="max-width:140px;">
                <label class="filter-label">Status</label>
                <select name="status" class="filter-control">
                    <option value="">Semua</option>
                    <option value="pending" {{ request('status')=='pending'?'selected':'' }}>Pending</option>
                    <option value="verified" {{ request('status')=='verified'?'selected':'' }}>Terverifikasi</option>
                    <option value="rejected" {{ request('status')=='rejected'?'selected':'' }}>Ditolak</option>
                </select>
            </div>
            <div class="filter-group" style="max-width:140px;">
                <label class="filter-label">Departemen</label>
                <select name="department" class="filter-control">
                    <option value="">Semua</option>
                    <option value="lenscase" {{ request('department')=='lenscase'?'selected':'' }}>Lenscase</option>
                    <option value="pouching" {{ request('department')=='pouching'?'selected':'' }}>Pouching</option>
                </select>
            </div>
            <div class="filter-group" style="max-width:170px;">
                <label class="filter-label">Shift</label>
                <select name="shift" class="filter-control">
                    <option value="">Semua</option>
                    <option value="1" {{ request('shift')=='1'?'selected':'' }}>Shift 1 (07:00-19:00)</option>
                    <option value="2" {{ request('shift')=='2'?'selected':'' }}>Shift 2 (19:00-07:00)</option>
                </select>
            </div>
            <div class="filter-group">
                <label class="filter-label">Dari</label>
                <input type="date" name="date_from" class="filter-control" value="{{ request('date_from') }}">
            </div>
            <div class="filter-group">
                <label class="filter-label">Sampai</label>
                <input type="date" name="date_to" class="filter-control" value="{{ request('date_to') }}">
            </div>
            <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-filter"></i> Filter</button>
            <a href="{{ route('leader.reports') }}" class="btn btn-outline btn-sm"><i class="fas fa-times"></i></a>
        </div>
    </form>

    @if($reports->isEmpty())
        <div class="empty-state"><i class="fas fa-inbox"></i><h3>Tidak Ada Laporan</h3><p>Tidak ada data yang sesuai filter.</p></div>
    @else
        <div class="table-wrapper" style="border:none;border-radius:0;">
            <table>
                <thead>
                    <tr>
                        <th>No. Laporan</th>
                        <th>Tgl Produksi</th>
                        <th>Waktu Submit</th>
                        <th>Dept</th>
                        <th>Shift</th>
                        <th>Produk</th>
                        <th>Operator</th>
                        <th>Qty Reject</th>
                        <th>% Reject</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($reports as $report)
                    <tr>
                        <td><span class="report-number">{{ $report->report_number }}</span></td>
                        <td style="white-space:nowrap">{{ $report->production_date->format('d/m/Y') }}</td>
                        <td style="white-space:nowrap;font-size:12px;color:var(--text-secondary)">{{ ($report->submitted_at ?? $report->created_at)->format('d/m/Y H:i:s') }}</td>
                        <td><span class="badge badge-{{ $report->department }}">{{ $report->department_display }}</span></td>
                        <td style="color:var(--text-secondary)">{{ $report->shift_display }}</td>
                        <td>{{ Str::limit($report->product_name, 25) }}</td>
                        <td style="color:var(--text-secondary)">{{ $report->operator->name }}</td>
                        <td><b style="color:var(--warning)">{{ number_format($report->total_reject_qty) }}</b></td>
                        <td><span style="color:{{ $report->reject_percentage>5?'var(--danger)':($report->reject_percentage>2?'var(--warning)':'var(--success)') }};font-weight:700">{{ $report->reject_percentage }}%</span></td>
                        <td><span class="badge badge-{{ $report->status }}">{{ $report->status_display }}</span></td>
                        <td>
                            <a href="{{ route('leader.report.show', $report) }}" class="btn btn-outline btn-sm">
                                @if($report->status === 'pending')
                                    <i class="fas fa-tasks"></i> Verifikasi
                                @else
                                    <i class="fas fa-eye"></i> Detail
                                @endif
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div style="padding:4px;">
            {{ $reports->links('vendor.pagination.custom') }}
        </div>
    @endif
</div>
@endsection
