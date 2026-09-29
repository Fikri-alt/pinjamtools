@extends('layouts.app')
@section('title','Login - PinjamTools')
@section('content')
<div class="max-w-md mx-auto bg-white rounded shadow p-6">
    <h1 class="text-2xl font-bold mb-4">Login</h1>
    <form method="POST" action="{{ route('login') }}">
        @csrf
        <label class="block mb-3 text-sm">Email
            <input type="email" name="email" value="{{ old('email') }}" required class="mt-1 w-full border rounded px-3 py-2">
        </label>
        <label class="block mb-3 text-sm">Password
            <span class="relative block mt-1">
                <input id="password" type="password" name="password" required class="w-full border rounded px-3 py-2 pr-10">
                <button type="button" id="togglePassword" aria-label="Tampilkan password" class="absolute inset-y-0 right-0 px-3 text-gray-500 hover:text-slate-900">
                    <span id="eyeOpen"><x-icon name="eye" /></span>
                    <span id="eyeClosed" class="hidden"><x-icon name="eye-off" /></span>
                </button>
            </span>
        </label>
        <label class="flex items-center gap-2 text-sm mb-4">
            <input type="checkbox" name="remember" value="1" {{ old('remember') ? 'checked' : '' }}> Ingat saya
        </label>
        <button class="w-full bg-slate-900 text-white py-2 rounded hover:bg-slate-700">Masuk</button>
    </form>
    <p class="text-sm mt-4">Belum punya akun? <a href="{{ route('register') }}" class="text-blue-600 underline">Daftar</a></p>
</div>
<script>
document.getElementById('togglePassword').addEventListener('click', function () {
    var input = document.getElementById('password');
    var show = input.type === 'password';
    input.type = show ? 'text' : 'password';
    document.getElementById('eyeOpen').classList.toggle('hidden', show);
    document.getElementById('eyeClosed').classList.toggle('hidden', !show);
    this.setAttribute('aria-label', show ? 'Sembunyikan password' : 'Tampilkan password');
});
</script>
@endsection
