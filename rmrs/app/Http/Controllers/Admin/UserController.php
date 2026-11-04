<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    private const ROLES = 'operator,line_leader,supervisor,admin';
    private const DEPARTMENTS = 'lenscase,pouching,all';

    public function index(Request $request)
    {
        $query = User::query();

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        $users = $query->orderBy('role')->orderBy('name')->paginate(20)->withQueryString();

        return view('admin.users', compact('users'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['required', 'in:' . self::ROLES],
            'department' => ['required', 'in:' . self::DEPARTMENTS],
        ]);

        User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'], // hashed via model cast
            'role' => $data['role'],
            'department' => $data['department'],
            'is_active' => true,
        ]);

        return redirect()->route('admin.users')->with('success', 'User berhasil ditambahkan.');
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:8'],
            'role' => ['required', 'in:' . self::ROLES],
            'department' => ['required', 'in:' . self::DEPARTMENTS],
            'is_active' => ['required', 'boolean'],
        ]);

        if ($user->id === auth()->id() && (!$data['is_active'] || $data['role'] !== 'admin')) {
            return back()->with('error', 'Anda tidak dapat menonaktifkan atau mengubah role akun Anda sendiri.');
        }

        $payload = [
            'name' => $data['name'],
            'email' => $data['email'],
            'role' => $data['role'],
            'department' => $data['department'],
            'is_active' => $data['is_active'],
        ];
        if (!empty($data['password'])) {
            $payload['password'] = $data['password']; // hashed via model cast
        }

        $user->update($payload);

        return redirect()->route('admin.users')->with('success', 'User berhasil diperbarui.');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Anda tidak dapat menghapus akun sendiri.');
        }

        // Laporan milik operator ikut terhapus bila user dihapus, jadi lindungi data laporan.
        if ($user->rejectReports()->withTrashed()->exists()) {
            return back()->with('error', 'User ini sudah memiliki laporan. Nonaktifkan akun saja agar data laporan tetap tersimpan.');
        }

        $user->delete();

        return redirect()->route('admin.users')->with('success', 'User berhasil dihapus.');
    }
}
