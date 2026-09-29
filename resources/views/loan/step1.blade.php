@extends('layouts.app')
@section('title','Pengajuan Step 1 - Data Diri')
@section('content')
<h1 class="text-2xl font-bold mb-1">Pengajuan Peminjaman - Step 1: Data Diri</h1>
<p class="text-sm text-gray-500 mb-4">Langkah 1 dari 3</p>
<div class="max-w-lg bg-white rounded shadow p-6">
    <form method="POST" action="{{ route('pinjam.step1.post') }}">
        @csrf
        <label class="block mb-3 text-sm">Nama Lengkap
            <input type="text" name="nama" value="{{ old('nama', $data['nama'] ?? '') }}" required class="mt-1 w-full border rounded px-3 py-2">
        </label>
        <label class="block mb-3 text-sm">Email
            <input type="email" name="email" value="{{ old('email', $data['email'] ?? '') }}" required class="mt-1 w-full border rounded px-3 py-2">
        </label>
        <label class="block mb-3 text-sm">No. HP (08..., 10-15 digit)
            <input type="text" name="no_hp" value="{{ old('no_hp', $data['no_hp'] ?? '') }}" required class="mt-1 w-full border rounded px-3 py-2">
        </label>
        <label class="block mb-4 text-sm">Departemen
            <select name="department_id" required class="mt-1 w-full border rounded px-3 py-2">
                <option value="">-- Pilih --</option>
                @foreach($departments as $d)
                    <option value="{{ $d->id }}" {{ old('department_id', $data['department_id'] ?? '')==$d->id?'selected':'' }}>{{ $d->nama }}</option>
                @endforeach
            </select>
        </label>
        <button class="bg-slate-900 text-white px-4 py-2 rounded">Lanjut &rarr;</button>
    </form>
</div>
@endsection
