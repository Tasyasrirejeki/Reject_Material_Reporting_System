@extends('layouts.app')
@section('title', $report ? 'Edit Laporan ' . $report->report_number : 'Input Laporan Reject')
@section('page-title', $report ? 'Edit Laporan Reject' : 'Input Laporan Reject')

@push('styles')
<style>
    .create-form { max-width: 900px; }
    .section-title {
        font-size: 13px;
        font-weight: 700;
        color: var(--primary);
        text-transform: uppercase;
        letter-spacing: 1px;
        margin-bottom: 16px;
        padding-bottom: 8px;
        border-bottom: 1px solid var(--border);
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .defects-list { display: flex; flex-direction: column; gap: 12px; }
    .qty-display {
        font-family: 'JetBrains Mono', monospace;
        font-size: 13px;
        color: var(--warning);
        font-weight: 600;
    }
</style>
@endpush

@section('content')
<div class="create-form">
    <div style="margin-bottom:20px;display:flex;align-items:center;gap:16px;">
        @if($report)
            <a href="{{ route('operator.reports.show', $report) }}" class="btn btn-outline btn-sm"><i class="fas fa-arrow-left"></i> Kembali</a>
        @else
            <a href="{{ route('operator.reports.index') }}" class="btn btn-outline btn-sm"><i class="fas fa-list-alt"></i> Riwayat</a>
        @endif
        <div>
            <div style="font-size:18px;font-weight:800;">{{ $report ? 'Edit Laporan ' . $report->report_number : 'Input Laporan Reject Baru' }}</div>
            <div style="font-size:12px;color:var(--text-secondary)">Departemen: <b id="deptLabel" style="color:var(--secondary)">{{ ucfirst($department) }}</b></div>
        </div>
        <div style="margin-left:auto;text-align:right;">
            @if($report)
                <div style="font-size:11px;color:var(--text-secondary);text-transform:uppercase;letter-spacing:.5px;">Disubmit</div>
                <div style="font-size:14px;font-weight:700;color:var(--secondary);font-variant-numeric:tabular-nums;">{{ ($report->submitted_at ?? $report->created_at)->format('d/m/Y H:i:s') }}</div>
            @else
                <div id="liveClock" style="font-size:15px;font-weight:700;color:var(--secondary);font-variant-numeric:tabular-nums;">{{ now()->format('d/m/Y H:i:s') }}</div>
            @endif
        </div>
    </div>

    @if($report)
    <div class="alert alert-warning fade-in" style="margin-bottom:20px;background:rgba(245,158,11,0.1);border:1px solid rgba(245,158,11,0.3);color:var(--warning);border-radius:var(--radius);padding:12px 16px;display:flex;gap:10px;align-items:flex-start;font-size:13px;">
        <i class="fas fa-info-circle" style="margin-top:2px;"></i>
        <div>Setelah disimpan, laporan yang diedit akan berstatus <b>Menunggu Verifikasi</b> dan perlu diperiksa oleh Line Leader.</div>
    </div>
    @endif

    <form method="POST" action="{{ $report ? route('operator.reports.update', $report) : route('operator.reports.store') }}" id="reportForm">
        @csrf
        @if($report) @method('PUT') @endif

        {{-- Section 1: Info Produksi --}}
        <div class="card fade-in" style="margin-bottom:20px;">
            <div class="card-body">
                <div class="section-title"><i class="fas fa-industry"></i> Informasi Produksi</div>
                <div class="form-row form-row-3">
                    <div class="form-group">
                        <label class="form-label required">Departemen</label>
                        <select name="department" class="form-control" required onchange="updateDefectTypes(this.value)">
                            <option value="lenscase" {{ $department === 'lenscase' ? 'selected' : '' }}>Lenscase</option>
                            <option value="pouching" {{ $department === 'pouching' ? 'selected' : '' }}>Pouching</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label required">Shift</label>
                        <select name="shift" id="shiftSelect" class="form-control" required>
                            <option value="1" {{ old('shift', $currentShift) == '1' ? 'selected' : '' }}>Shift 1 (07:00 - 19:00)</option>
                            <option value="2" {{ old('shift', $currentShift) == '2' ? 'selected' : '' }}>Shift 2 (19:00 - 07:00)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label required">Tanggal Produksi</label>
                        <input type="date" name="production_date" class="form-control" value="{{ old('production_date', $report ? $report->production_date->format('Y-m-d') : date('Y-m-d')) }}" max="{{ date('Y-m-d') }}" required>
                    </div>
                </div>

                <div class="form-row form-row-3">
                    <div class="form-group">
                        <label class="form-label required">Jenis Material</label>
                        <input type="text" name="product_name" class="form-control" value="{{ old('product_name', $report->product_name ?? '') }}" placeholder="e.g. Lens Case Type A" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Batch Material</label>
                        <input type="text" name="product_code" class="form-control" value="{{ old('product_code', $report->product_code ?? '') }}" placeholder="e.g. LC-001">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label required">Total Qty Produksi</label>
                        <input type="number" name="total_production_qty" class="form-control" value="{{ old('total_production_qty', $report->total_production_qty ?? '') }}" placeholder="Contoh: 1000" min="1" required onchange="updateSummary()">
                    </div>
                </div>
            </div>
        </div>

        {{-- Section 2: Detail Defect --}}
        <div class="card fade-in fade-in-2" style="margin-bottom:20px;">
            <div class="card-header">
                <div class="card-title"><i class="fas fa-bug"></i> Detail Jenis Reject</div>
                <div style="font-size:12px;color:var(--text-secondary)">Total Reject: <span class="qty-display" id="totalRejectDisplay">0</span> pcs</div>
            </div>
            <div class="card-body">
                <div class="defects-list" id="defectsList">
                    {{-- Template row akan di-append via JS --}}
                </div>

                <button type="button" class="btn btn-outline" style="margin-top:16px;width:100%;border-style:dashed;" onclick="addDefectRow()">
                    <i class="fas fa-plus"></i> Tambah Jenis Defect
                </button>
            </div>
        </div>

        <div style="display:flex;gap:12px;justify-content:flex-end;">
            <a href="{{ $report ? route('operator.reports.show', $report) : route('operator.reports.index') }}" class="btn btn-outline">Batal</a>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-paper-plane"></i> {{ $report ? 'Simpan Perubahan' : 'Kirim Laporan' }}
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
const lensDefects = [
    'Bubble / Gelembung', 'Dimensi Tidak Sesuai', 'Warna Tidak Sesuai', 'Berminyak', 'Kontaminasi / Foreign Material','Edge Chip','String', 'Other'
];

const pouchDefects = [
    'Seal Bocor','Seal Tidak Rapi','Pouch Robek', 'Illegible', 'Blister', 'Redstain', 'Particle','Edge Cut', 'Smudge', 'Other'
];

let currentDept = '{{ $department }}';
let defectCount = 0;

function getDefects(dept) {
    return dept === 'pouching' ? pouchDefects : lensDefects;
}

function buildDefectOptions(dept) {
    return getDefects(dept).map(d => `<option value="${d}">${d}</option>`).join('');
}

function addDefectRow() {
    const idx = defectCount++;
    const options = buildDefectOptions(currentDept);
    const html = `
        <div class="defect-row" id="defect-${idx}" data-idx="${idx}">
            <div class="defect-row-header">
                <span class="defect-row-num"><i class="fas fa-bug"></i> Defect #${idx + 1}</span>
                ${idx > 0 ? `<button type="button" class="btn-remove-defect" onclick="removeDefect(${idx})"><i class="fas fa-trash"></i> Hapus</button>` : ''}
            </div>
            <div class="form-row">
                <div class="form-group" style="margin-bottom:0">
                    <label class="form-label required">Jenis Defect</label>
                    <select name="details[${idx}][defect_type]" class="form-control" required>
                        <option value="">-- Pilih Jenis --</option>
                        ${options}
                    </select>
                </div>
                <div class="form-group" style="margin-bottom:0">
                    <label class="form-label required">Qty Reject (pcs)</label>
                    <input type="number" name="details[${idx}][quantity]" class="form-control qty-input" min="1" placeholder="0" required oninput="updateSummary()">
                </div>
            </div>
            <div class="form-row" style="margin-top:10px;">
                <div class="form-group" style="margin-bottom:0">
                    <label class="form-label">Deskripsi</label>
                    <input type="text" name="details[${idx}][description]" class="form-control" placeholder="Catatan tambahan untuk defect ini">
                </div>
            </div>
        </div>`;
    document.getElementById('defectsList').insertAdjacentHTML('beforeend', html);
}

function removeDefect(idx) {
    document.getElementById('defect-' + idx)?.remove();
    updateSummary();
}

function updateSummary() {
    let total = 0;
    document.querySelectorAll('.qty-input').forEach(el => { total += parseInt(el.value) || 0; });
    document.getElementById('totalRejectDisplay').textContent = total.toLocaleString('id-ID');
}

function updateDefectTypes(dept) {
    currentDept = dept;
    const lbl = document.getElementById('deptLabel');
    if (lbl) lbl.textContent = dept.charAt(0).toUpperCase() + dept.slice(1);
    document.querySelectorAll('[name$="[defect_type]"]').forEach(sel => {
        const current = sel.value;
        sel.innerHTML = '<option value="">-- Pilih Jenis --</option>' + buildDefectOptions(dept);
        // Try to restore old value
        if (current) sel.value = current;
    });
}

// Live clock (waktu submit sebenarnya dicatat server saat laporan dikirim)
function tickClock() {
    const n = new Date();
    const p = v => String(v).padStart(2, '0');
    document.getElementById('liveClock').textContent =
        `${p(n.getDate())}/${p(n.getMonth() + 1)}/${n.getFullYear()} ${p(n.getHours())}:${p(n.getMinutes())}:${p(n.getSeconds())}`;
}
setInterval(tickClock, 1000);
tickClock();

// Init with one row
addDefectRow();
</script>
@endpush
