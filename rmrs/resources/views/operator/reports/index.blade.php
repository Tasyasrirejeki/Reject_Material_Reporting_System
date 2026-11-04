@extends('layouts.app')
@section('title', 'Riwayat Laporan Saya')
@section('page-title', 'Riwayat Laporan')

@section('content')
<div class="card fade-in">
    <div class="card-header">
        <div class="card-title"><i class="fas fa-list-alt"></i> Riwayat Laporan Saya</div>
        <a href="{{ route('operator.reports.create') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-plus"></i> Laporan Baru
        </a>
    </div>

    {{-- Filter --}}
    <form method="GET" style="padding:16px 20px;border-bottom:1px solid var(--border);">
        <div style="display:flex;flex-wrap:wrap;gap:12px;align-items:flex-end;">
            <div class="filter-group">
                <label class="filter-label">Cari</label>
                <input type="text" name="search" class="filter-control" value="{{ request('search') }}" placeholder="No. laporan / produk...">
            </div>
            <div class="filter-group" style="max-width:150px;">
                <label class="filter-label">Status</label>
                <select name="status" class="filter-control">
                    <option value="">Semua</option>
                    <option value="pending" {{ request('status')=='pending'?'selected':'' }}>Pending</option>
                    <option value="verified" {{ request('status')=='verified'?'selected':'' }}>Terverifikasi</option>
                    <option value="rejected" {{ request('status')=='rejected'?'selected':'' }}>Ditolak</option>
                </select>
            </div>
            <div class="filter-group">
                <label class="filter-label">Dari Tanggal</label>
                <input type="date" name="date_from" class="filter-control" value="{{ request('date_from') }}">
            </div>
            <div class="filter-group">
                <label class="filter-label">Sampai Tanggal</label>
                <input type="date" name="date_to" class="filter-control" value="{{ request('date_to') }}">
            </div>
            <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-filter"></i> Filter</button>
            <a href="{{ route('operator.reports.index') }}" class="btn btn-outline btn-sm"><i class="fas fa-times"></i> Reset</a>
        </div>
    </form>

    @if($reports->isEmpty())
        <div class="empty-state">
            <i class="fas fa-inbox"></i>
            <h3>Tidak Ada Laporan</h3>
            <p>Belum ada laporan yang sesuai filter.</p>
        </div>
    @else
        <div class="table-wrapper" style="border:none;border-radius:0;">
            <table>
                <thead>
                    <tr>
                        <th>No. Laporan</th>
                        <th>Tgl Produksi</th>
                        <th>Waktu Submit</th>
                        <th>Shift</th>
                        <th>Material</th>
                        <th>Jenis Material</th>
                        <th>Qty Reject</th>
                        <th>Jml Defect</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($reports as $report)
                    <tr>
                        <td><span class="report-number">{{ $report->report_number }}</span></td>
                        <td>{{ $report->production_date->format('d/m/Y') }}</td>
                        <td style="white-space:nowrap;font-size:12px;color:var(--text-secondary)">{{ ($report->submitted_at ?? $report->created_at)->format('d/m/Y H:i:s') }}</td>
                        <td><span class="badge badge-lenscase">{{ $report->shift_display }}</span></td>
                        <td><span class="badge badge-{{ $report->department }}">{{ $report->department_display }}</span></td>
                        <td>{{ $report->product_name }}</td>
                        <td><b style="color:var(--warning)">{{ number_format($report->total_reject_qty) }}</b></td>
                        <td style="text-align:center">{{ $report->details->count() }}</td>
                        <td><span class="badge badge-{{ $report->status }}">{{ $report->status_display }}</span></td>
                        <td>
                            <a href="{{ route('operator.reports.show', $report) }}" class="btn btn-outline btn-sm">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('operator.reports.edit', $report) }}" class="btn btn-outline btn-sm" title="Edit">
                                <i class="fas fa-edit"></i>
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
