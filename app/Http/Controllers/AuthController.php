<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Department;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View
    {
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email', 'max:150'],
            'password' => ['required', 'string'],
        ]);

        $remember = $request->boolean('remember');

        if (! Auth::attempt($credentials, $remember)) {
            return back()->withErrors(['email' => 'Email atau password salah.'])->onlyInput('email', 'remember');
        }

        $request->session()->regenerate();

        $user = Auth::user();

        AuditLog::create([
            'user_id' => $user->id,
            'aksi' => 'login',
            'model_type' => User::class,
            'model_id' => $user->id,
            'detail' => ['email' => $user->email],
            'ip' => $request->ip(),
        ]);

        // Redirect sesuai role
        if (in_array($user->role, ['admin', 'supervisor', 'super_admin'], true)) {
            return redirect()->intended('/admin');
        }

        return redirect()->intended('/');
    }

    public function showRegister(): View
    {
        $departments = Department::orderBy('nama')->get();

        return view('auth.register', compact('departments'));
    }

    public function register(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:150', 'unique:users,email'],
            'no_hp' => ['required', 'regex:/^08[0-9]{8,13}$/', 'max:25'],
            'department_id' => ['required', 'exists:departments,id'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'no_hp.regex' => 'Nomor HP harus format Indonesia (diawali 08, total 10-15 digit).',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'no_hp' => $validated['no_hp'],
            'department_id' => $validated['department_id'],
            'password' => $validated['password'],
            'role' => 'peminjam',
        ]);

        AuditLog::create([
            'user_id' => $user->id,
            'aksi' => 'register',
            'model_type' => User::class,
            'model_id' => $user->id,
            'detail' => ['email' => $user->email],
            'ip' => $request->ip(),
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect('/')->with('success', 'Registrasi berhasil. Selamat datang, ' . $user->name . '!');
    }

    public function logout(Request $request): RedirectResponse
    {
        $userId = Auth::id();

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($userId) {
            AuditLog::create([
                'user_id' => $userId,
                'aksi' => 'logout',
                'model_type' => User::class,
                'model_id' => $userId,
                'detail' => null,
                'ip' => $request->ip(),
            ]);
        }

        return redirect('/')->with('success', 'Anda telah logout.');
    }
}
