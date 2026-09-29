<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(): View
    {
        $users = User::with('department')->orderBy('name')->paginate(15);

        return view('admin.users.index', compact('users'));
    }

    public function create(): View
    {
        $departments = Department::orderBy('nama')->get();
        $roles = ['peminjam', 'admin', 'supervisor', 'super_admin'];

        return view('admin.users.form', ['user' => new User(), 'departments' => $departments, 'roles' => $roles]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:150', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'max:255'],
            'no_hp' => ['nullable', 'string', 'max:25'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'role' => ['required', 'in:peminjam,admin,supervisor,super_admin'],
        ]);

        // Hanya super_admin boleh membuat user dengan role selain peminjam
        if (($validated['role'] ?? 'peminjam') !== 'peminjam' && ! Auth::user()->isSuperAdmin()) {
            return back()->with('error', 'Hanya super_admin yang boleh menentukan role admin/supervisor/super_admin.')->withInput();
        }

        User::create($validated);

        return redirect()->route('admin.users.index')->with('success', 'User dibuat.');
    }

    public function edit(int $id): View
    {
        $user = User::findOrFail($id);
        $departments = Department::orderBy('nama')->get();
        $roles = ['peminjam', 'admin', 'supervisor', 'super_admin'];

        return view('admin.users.form', compact('user', 'departments', 'roles'));
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $user = User::findOrFail($id);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:150', 'unique:users,email,'.$user->id],
            'password' => ['nullable', 'string', 'min:8', 'max:255'],
            'no_hp' => ['nullable', 'string', 'max:25'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'role' => ['sometimes', 'in:peminjam,admin,supervisor,super_admin'],
        ]);

        // Cegah perubahan role oleh non super_admin
        if (array_key_exists('role', $validated) && $validated['role'] !== $user->role) {
            if (! Auth::user()->isSuperAdmin()) {
                return back()->with('error', 'Hanya super_admin yang boleh mengubah role.')->withInput();
            }
        }

        if (empty($validated['password'])) {
            unset($validated['password']);
        }

        $user->update($validated);

        return redirect()->route('admin.users.index')->with('success', 'User diperbarui.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $user = User::findOrFail($id);

        if (! Auth::user()->isSuperAdmin()) {
            abort(403, 'Hanya super_admin yang boleh menghapus user.');
        }

        if ($user->id === Auth::id()) {
            return back()->with('error', 'Tidak bisa menghapus akun sendiri.');
        }

        $user->delete();

        return redirect()->route('admin.users.index')->with('success', 'User dihapus.');
    }
}
