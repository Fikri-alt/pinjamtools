@extends('layouts.app')
@section('title','Register - PinjamTools')
@section('content')
<div class="max-w-md mx-auto bg-white rounded shadow p-6">
    <h1 class="text-2xl font-bold mb-4">Register Peminjam</h1>
    <form method="POST" action="{{ route('register') }}">
        @csrf
        <label class="block mb-3 text-sm">Nama
            <input type="text" name="name" value="{{ old('name') }}" required class="mt-1 w-full border rounded px-3 py-2">
        </label>
        <label class="block mb-3 text-sm">Email
            <input type="email" name="email" value="{{ old('email') }}" required class="mt-1 w-full border rounded px-3 py-2">
        </label>
        <label class="block mb-3 text-sm">No. HP (08..., 10-15 digit)
            <input type="text" name="no_hp" value="{{ old('no_hp') }}" required placeholder="08xxxxxxxxxx" class="mt-1 w-full border rounded px-3 py-2">
        </label>
        <label class="block mb-3 text-sm">Departemen
            <select name="department_id" required class="mt-1 w-full border rounded px-3 py-2">
                <option value="">-- Pilih --</option>
                @foreach($departments as $d)
                    <option value="{{ $d->id }}" {{ old('department_id')==$d->id?'selected':'' }}>{{ $d->nama }}</option>
                @endforeach
            </select>
        </label>
        <label class="block mb-3 text-sm">Password (min 8)
            <input type="password" name="password" required class="mt-1 w-full border rounded px-3 py-2">
        </label>
        <label class="block mb-4 text-sm">Konfirmasi Password
            <input type="password" name="password_confirmation" required class="mt-1 w-full border rounded px-3 py-2">
        </label>
        <button class="w-full bg-slate-900 text-white py-2 rounded hover:bg-slate-700">Daftar</button>
    </form>
    <p class="text-sm mt-4">Sudah punya akun? <a href="{{ route('login') }}" class="text-blue-600 underline">Login</a></p>
</div>
@endsection
