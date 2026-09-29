@extends('layouts.app')
@section('title','Cek Status - PinjamTools')
@section('content')
<div class="max-w-md mx-auto bg-white rounded shadow p-6">
    <h1 class="text-2xl font-bold mb-4">Cek Status Peminjaman</h1>
    <form method="POST" action="{{ route('status.cari') }}">
        @csrf
        <label class="block text-sm mb-3">Kode Peminjaman (contoh PJM-20250101-0001)
            <input type="text" name="kode" value="{{ old('kode') }}" required placeholder="PJM-YYYYMMDD-XXXX" class="mt-1 w-full border rounded px-3 py-2 font-mono">
        </label>
        <button class="w-full bg-slate-900 text-white py-2 rounded">Cek Status</button>
    </form>
</div>
@endsection
