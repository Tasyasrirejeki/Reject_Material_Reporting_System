@extends('layouts.app')
@section('title', 'Verifikasi ' . $report->report_number)
@section('page-title', 'Detail & Verifikasi Laporan')

@section('content')
<div style="max-width:900px;">
    <div style="display:flex;align-items:center;gap:16px;margin-bottom:20px;">
        <a href="{{ route('leader.reports') }}" class="btn btn-outline btn-sm"><i class="fas fa-arrow-left"></i> Kembali</a>
        <div>
            <div style="font-size:18px;font-weight:800;">{{ $report->report_number }}</div>
            <div style="font-size:12px;color:var(--text-secondary)">{{ $report->production_date->format('d F Y') }} · {{ $report->shift_display }} · {{ $report->operator->name }}</div>
        </div>
        <div style="margin-left:auto;display:flex;align-items:center;gap:10px;">
            <span class="badge badge-{{ $report->status }}" style="font-size:13px;padding:6px 16px;">{{ $report->status_display }}</span>
        </div>
    </div>

    {{-- Stats --}}
    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:16px;" class="fade-in">
        <div class="stat-card purple"><div class="stat-icon"><i class="fas fa-industry"></i></div><div class="stat-value">{{ $report->department_display }}</div><div class="stat-label">Departemen</div></div>
        <div class="stat-card teal"><div class="stat-icon"><i class="fas fa-boxes"></i></div><div class="stat-value">{{ number_format($report->total_production_qty) }}</div><div class="stat-label">Qty Produksi</div></div>
        <div class="stat-card yellow"><div class="stat-icon"><i class="fas fa-exclamation-triangle"></i></div><div class="stat-value">{{ number_format($report->total_reject_qty) }}</div><div class="stat-label">Qty Reject</div></div>
        <div class="stat-card {{ $report->reject_percentage > 5 ? 'red' : 'green' }}"><div class="stat-icon"><i class="fas fa-percent"></i></div><div class="stat-value">{{ $report->reject_percentage }}%</div><div class="stat-label">% Reject</div></div>
    </div>

    {{-- Product info --}}
    <div class="card fade-in fade-in-2" style="margin-bottom:16px;">
        <div class="card-header"><div class="card-title"><i class="fas fa-box"></i> Info Produk</div></div>
        <div class="card-body">
            <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px;">
                @foreach([['Nama Produk',$report->product_name],['Kode Produk',$report->product_code??'-'],['Lot Number',$report->lot_number??'-'],['No. Mesin',$report->machine_number??'-'],['Dibuat Oleh',$report->operator->name],['Waktu Submit',($report->submitted_at ?? $report->created_at)->format('d/m/Y H:i:s')]] as [$l,$v])
                <div>
                    <div style="font-size:11px;font-weight:700;color:var(--text-secondary);text-transform:uppercase;letter-spacing:0.5px;margin-bottom:4px;">{{ $l }}</div>
                    <div style="font-size:14px;font-weight:600;">{{ $v }}</div>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Defect details --}}
    <div class="card fade-in fade-in-3" style="margin-bottom:16px;">
        <div class="card-header"><div class="card-title"><i class="fas fa-bug"></i> Detail Defect</div></div>
        <div class="table-wrapper" style="border:none;border-radius:0;">
            <table>
                <thead><tr><th>#</th><th>Jenis Defect</th><th>Kategori</th><th>Qty (pcs)</th><th>Lokasi</th><th>Deskripsi</th></tr></thead>
                <tbody>
                    @foreach($report->details as $i => $d)
                    <tr>
                        <td style="color:var(--text-secondary)">{{ $i+1 }}</td>
                        <td><b>{{ $d->defect_type }}</b></td>
                        <td><span class="badge badge-{{ $d->defect_category }}">{{ $d->category_display }}</span></td>
                        <td><b style="color:var(--warning);font-family:JetBrains Mono,monospace">{{ number_format($d->quantity) }}</b></td>
                        <td style="color:var(--text-secondary)">{{ $d->defect_location??'-' }}</td>
                        <td style="color:var(--text-secondary)">{{ $d->description??'-' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Operator notes --}}
    <div class="card fade-in fade-in-4" style="margin-bottom:16px;">
        <div class="card-header"><div class="card-title"><i class="fas fa-sticky-note"></i> Catatan Operator</div></div>
        <div class="card-body">
            <div style="background:var(--bg-elevated);border-radius:8px;padding:12px;font-size:13px;">{{ $report->operator_notes ?: 'Tidak ada catatan.' }}</div>
        </div>
    </div>

    {{-- Verification action --}}
    @if($report->status === 'pending')
    <div class="card fade-in" style="border-color:rgba(108,99,255,0.3);">
        <div class="card-header">
            <div class="card-title"><i class="fas fa-tasks"></i> Tindakan Verifikasi</div>
        </div>
        <div class="card-body">
            <div style="margin-bottom:16px;">
                <label class="form-label">Catatan Line Leader</label>
                <textarea id="leaderNotes" class="form-control" rows="3" placeholder="Tulis catatan verifikasi, koreksi, atau keterangan tambahan..."></textarea>
            </div>
            <div style="display:flex;gap:12px;">
                <form method="POST" action="{{ route('leader.report.verify', $report) }}" id="verifyForm" style="flex:1">
                    @csrf
                    <input type="hidden" name="leader_notes" id="verifyNotes">
                    <button type="button" onclick="submitVerify()" class="btn btn-success" style="width:100%">
                        <i class="fas fa-check-circle"></i> Verifikasi & Setujui
                    </button>
                </form>
                <form method="POST" action="{{ route('leader.report.reject', $report) }}" id="rejectForm" style="flex:1">
                    @csrf
                    <input type="hidden" name="leader_notes" id="rejectNotes">
                    <button type="button" onclick="submitReject()" class="btn btn-danger" style="width:100%">
                        <i class="fas fa-times-circle"></i> Kembalikan ke Operator
                    </button>
                </form>
            </div>
        </div>
    </div>
    @else
    <div class="card fade-in" style="border-color:{{ $report->status==='verified'?'rgba(16,185,129,0.3)':'rgba(239,68,68,0.3)' }}">
        <div class="card-header">
            <div class="card-title"><i class="fas fa-{{ $report->status==='verified'?'check':'times' }}-circle" style="color:{{ $report->status==='verified'?'var(--success)':'var(--danger)' }}"></i>
                {{ $report->status==='verified'?'Laporan Telah Diverifikasi':'Laporan Telah Dikembalikan' }}
            </div>
            <div style="font-size:12px;color:var(--text-secondary)">oleh {{ $report->lineLeader->name ?? '-' }} · {{ $report->verified_at?->format('d/m/Y H:i') }}</div>
        </div>
        <div class="card-body">
            <div style="background:var(--bg-elevated);border-radius:8px;padding:12px;font-size:13px;">{{ $report->leader_notes ?: 'Tidak ada catatan.' }}</div>
        </div>
    </div>
    @endif
</div>

<div id="confirmModal" class="modal-overlay">
    <div class="modal">
        <div class="modal-header">
            <div class="modal-title" id="confirmTitle">Konfirmasi</div>
            <button type="button" class="modal-close" onclick="closeModal()">✕</button>
        </div>
        <div class="modal-body">
            <p id="confirmMsg" style="font-size:14px;color:var(--text-secondary);"></p>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-outline" onclick="closeModal()">Batal</button>
            <button type="button" class="btn" id="confirmBtn" onclick="executeAction()"></button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
let pendingAction = null;

function submitVerify() {
    document.getElementById('confirmTitle').textContent = '✅ Konfirmasi Verifikasi';
    document.getElementById('confirmMsg').textContent = 'Apakah Anda yakin ingin memverifikasi laporan ini?';
    document.getElementById('confirmBtn').textContent = 'Ya, Verifikasi';
    document.getElementById('confirmBtn').className = 'btn btn-success';
    pendingAction = 'verify';
    document.getElementById('confirmModal').classList.add('open');
}

function submitReject() {
    const notes = document.getElementById('leaderNotes').value;
    if (!notes.trim()) {
        alert('Harap isi catatan alasan pengembalian laporan.');
        return;
    }
    document.getElementById('confirmTitle').textContent = '↩️ Konfirmasi Pengembalian';
    document.getElementById('confirmMsg').textContent = 'Laporan akan dikembalikan ke operator. Pastikan catatan alasan sudah diisi.';
    document.getElementById('confirmBtn').textContent = 'Ya, Kembalikan';
    document.getElementById('confirmBtn').className = 'btn btn-danger';
    pendingAction = 'reject';
    document.getElementById('confirmModal').classList.add('open');
}

function executeAction() {
    const notes = document.getElementById('leaderNotes').value;
    if (pendingAction === 'verify') {
        document.getElementById('verifyNotes').value = notes;
        document.getElementById('verifyForm').submit();
    } else {
        document.getElementById('rejectNotes').value = notes;
        document.getElementById('rejectForm').submit();
    }
}

function closeModal() {
    document.getElementById('confirmModal').classList.remove('open');
    pendingAction = null;
}

document.getElementById('confirmModal').addEventListener('click', function(e) {
    if (e.target === this) closeModal();
});
</script>
@endpush
