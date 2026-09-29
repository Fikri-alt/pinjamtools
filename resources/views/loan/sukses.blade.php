@extends('layouts.app')
@section('title','Pengajuan Berhasil - PinjamTools')
@section('content')
<div class="max-w-lg mx-auto bg-white rounded shadow p-6 text-center">
    <div class="mb-2 text-green-600" style="font-size:3rem;line-height:1"><x-icon name="check-circle" /></div>
    <h1 class="text-2xl font-bold mb-2">Pengajuan Berhasil!</h1>
    <p class="text-gray-600 mb-4">Kode peminjaman Anda:</p>
    <div class="text-2xl font-mono font-bold bg-gray-100 rounded px-4 py-2 mb-4">{{ $loan->kode_pinjam }}</div>
    <p class="text-sm text-gray-500 mb-4">Simpan kode ini untuk mengecek status. Status awal: <b>{{ $loan->statusLabel() }}</b>. Stok baru dikurangi saat disetujui/handover.</p>
    <div class="flex justify-center gap-3">
        <a href="{{ route('status.show', $loan->kode_pinjam) }}" class="bg-slate-900 text-white px-4 py-2 rounded">Lihat Timeline</a>
        <a href="{{ route('katalog') }}" class="border px-4 py-2 rounded">Katalog</a>
    </div>
</div>
@endsection
