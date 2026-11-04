@extends('layouts.app')
@section('title', 'Manajemen User')
@section('page-title', 'Manajemen User')

@section('content')
<div class="card fade-in">
    <div class="card-header">
        <div class="card-title"><i class="fas fa-users-cog"></i> Daftar Pengguna Sistem</div>
        <button type="button" class="btn btn-primary btn-sm" onclick="openAddModal()">
            <i class="fas fa-user-plus"></i> Tambah User
        </button>
    </div>

    <form method="GET" style="padding:16px 20px;border-bottom:1px solid var(--border);">
        <div style="display:flex;flex-wrap:wrap;gap:12px;align-items:flex-end;">
            <div class="filter-group">
                <label class="filter-label">Cari</label>
                <input type="text" name="search" class="filter-control" value="{{ request('search') }}" placeholder="Nama / email...">
            </div>
            <div class="filter-group" style="max-width:160px;">
                <label class="filter-label">Role</label>
                <select name="role" class="filter-control">
                    <option value="">Semua</option>
                    @foreach(['operator' => 'Operator', 'line_leader' => 'Line Leader', 'supervisor' => 'Supervisor', 'admin' => 'Admin'] as $val => $label)
                        <option value="{{ $val }}" {{ request('role') === $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-filter"></i> Filter</button>
            <a href="{{ route('admin.users') }}" class="btn btn-outline btn-sm"><i class="fas fa-times"></i></a>
        </div>
    </form>

    <div class="table-wrapper" style="border:none;border-radius:0;">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nama</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Departemen</th>
                    <th>Status</th>
                    <th>Bergabung</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $i => $user)
                <tr>
                    <td style="color:var(--text-secondary)">{{ $users->firstItem() + $i }}</td>
                    <td>
                        <div style="display:flex;align-items:center;gap:10px;">
                            <div style="width:32px;height:32px;border-radius:50%;background:linear-gradient(135deg,var(--primary),var(--primary-dark));display:flex;align-items:center;justify-content:center;font-weight:700;font-size:12px;color:white;flex-shrink:0;">
                                {{ strtoupper(substr($user->name, 0, 2)) }}
                            </div>
                            <span style="font-weight:600">{{ $user->name }}</span>
                            @if($user->id === auth()->id())
                                <span class="badge badge-lenscase" style="font-size:9px;">Anda</span>
                            @endif
                        </div>
                    </td>
                    <td style="color:var(--text-secondary);font-size:12px;">{{ $user->email }}</td>
                    <td>
                        <span class="badge {{ match($user->role) { 'admin' => 'badge-pouching', 'supervisor' => 'badge-critical', 'line_leader' => 'badge-major', default => 'badge-verified' } }}">
                            {{ $user->role_display }}
                        </span>
                    </td>
                    <td><span class="badge badge-{{ $user->department === 'all' ? 'lenscase' : $user->department }}">{{ $user->department_display }}</span></td>
                    <td>
                        <span class="badge {{ $user->is_active ? 'badge-verified' : 'badge-rejected' }}">
                            {{ $user->is_active ? 'Aktif' : 'Nonaktif' }}
                        </span>
                    </td>
                    <td style="color:var(--text-secondary);font-size:12px;">{{ $user->created_at->format('d/m/Y') }}</td>
                    <td>
                        <button type="button" class="btn btn-outline btn-sm"
                            data-id="{{ $user->id }}"
                            data-name="{{ $user->name }}"
                            data-email="{{ $user->email }}"
                            data-role="{{ $user->role }}"
                            data-department="{{ $user->department }}"
                            data-active="{{ $user->is_active ? 1 : 0 }}"
                            data-self="{{ $user->id === auth()->id() ? 1 : 0 }}"
                            onclick="openEditModal(this)">
                            <i class="fas fa-edit"></i>
                        </button>
                        @if($user->id !== auth()->id())
                        <form method="POST" action="{{ route('admin.users.delete', $user) }}" style="display:inline;" onsubmit="return confirm('Hapus user {{ addslashes($user->name) }}?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
                        </form>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="8"><div class="empty-state"><i class="fas fa-users"></i><h3>Tidak Ada User</h3></div></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div style="padding:4px;">{{ $users->links('vendor.pagination.custom') }}</div>
</div>

{{-- Add User Modal --}}
<div class="modal-overlay" id="addModal">
    <div class="modal">
        <div class="modal-header">
            <div class="modal-title"><i class="fas fa-user-plus"></i> Tambah User Baru</div>
            <button type="button" class="modal-close" onclick="closeModals()">✕</button>
        </div>
        <form method="POST" action="{{ route('admin.users.store') }}">
            @csrf
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label required">Nama Lengkap</label>
                    <input type="text" name="name" class="form-control" required placeholder="Nama lengkap pengguna">
                </div>
                <div class="form-group">
                    <label class="form-label required">Email</label>
                    <input type="email" name="email" class="form-control" required placeholder="email@perusahaan.com">
                </div>
                <div class="form-group">
                    <label class="form-label required">Password</label>
                    <input type="password" name="password" class="form-control" required minlength="8" placeholder="Min. 8 karakter">
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label required">Role</label>
                        <select name="role" class="form-control" required>
                            <option value="operator">Operator</option>
                            <option value="line_leader">Line Leader</option>
                            <option value="supervisor">Supervisor</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label required">Departemen</label>
                        <select name="department" class="form-control" required>
                            <option value="all">All Departments</option>
                            <option value="lenscase">Lenscase</option>
                            <option value="pouching">Pouching</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModals()">Batal</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button>
            </div>
        </form>
    </div>
</div>

{{-- Edit User Modal --}}
<div class="modal-overlay" id="editModal">
    <div class="modal">
        <div class="modal-header">
            <div class="modal-title"><i class="fas fa-user-edit"></i> Edit User</div>
            <button type="button" class="modal-close" onclick="closeModals()">✕</button>
        </div>
        <form method="POST" id="editForm">
            @csrf @method('PUT')
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label required">Nama Lengkap</label>
                    <input type="text" name="name" id="editName" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label required">Email</label>
                    <input type="email" name="email" id="editEmail" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Password Baru</label>
                    <input type="password" name="password" id="editPassword" class="form-control" minlength="8" placeholder="Kosongkan jika tidak diubah">
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label required">Role</label>
                        <select name="role" id="editRole" class="form-control" required>
                            <option value="operator">Operator</option>
                            <option value="line_leader">Line Leader</option>
                            <option value="supervisor">Supervisor</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label required">Departemen</label>
                        <select name="department" id="editDept" class="form-control" required>
                            <option value="all">All Departments</option>
                            <option value="lenscase">Lenscase</option>
                            <option value="pouching">Pouching</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label required">Status</label>
                    <select name="is_active" id="editActive" class="form-control" required>
                        <option value="1">Aktif</option>
                        <option value="0">Nonaktif</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModals()">Batal</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
const baseUrl = '{{ url("admin/users") }}';

function openAddModal() {
    document.getElementById('addModal').classList.add('open');
}

function openEditModal(btn) {
    const d = btn.dataset;
    document.getElementById('editForm').action = baseUrl + '/' + d.id;
    document.getElementById('editName').value = d.name;
    document.getElementById('editEmail').value = d.email;
    document.getElementById('editPassword').value = '';
    document.getElementById('editRole').value = d.role;
    document.getElementById('editDept').value = d.department;
    document.getElementById('editActive').value = d.active;
    // Akun sendiri: role & status dikunci agar admin tidak mengunci dirinya sendiri
    const self = d.self === '1';
    document.getElementById('editRole').disabled = self;
    document.getElementById('editActive').disabled = self;
    if (self) {
        // disabled field tidak ikut terkirim, kirim lewat hidden input
        ensureHidden('role', d.role);
        ensureHidden('is_active', d.active);
    } else {
        document.querySelectorAll('#editForm .self-hidden').forEach(el => el.remove());
    }
    document.getElementById('editModal').classList.add('open');
}

function ensureHidden(name, value) {
    document.querySelectorAll('#editForm .self-hidden[name="' + name + '"]').forEach(el => el.remove());
    const input = document.createElement('input');
    input.type = 'hidden';
    input.className = 'self-hidden';
    input.name = name;
    input.value = value;
    document.getElementById('editForm').appendChild(input);
}

function closeModals() {
    document.querySelectorAll('.modal-overlay').forEach(m => m.classList.remove('open'));
}

document.querySelectorAll('.modal-overlay').forEach(el => {
    el.addEventListener('click', function(e) {
        if (e.target === this) closeModals();
    });
});
</script>
@endpush
