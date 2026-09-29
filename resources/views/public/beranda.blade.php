@extends('layouts.app')
@section('title','Beranda - PinjamTools')
@section('content')
<div class="bg-slate-900 text-white rounded p-8 mb-6">
    <h1 class="text-3xl font-bold mb-2">Pinjam Alat Kerja dengan Mudah</h1>
    <p class="text-gray-300">Tersedia hari ini ({{ $today }}): <span class="font-bold text-yellow-300">{{ $tersediaHariIni }} barang</span></p>
    <div class="mt-4 flex gap-3">
        <a href="{{ route('katalog') }}" class="bg-yellow-500 text-slate-900 px-4 py-2 rounded font-semibold">Lihat Katalog</a>
        <a href="{{ route('pinjam.step1') }}" class="border border-white/40 px-4 py-2 rounded">Ajukan Peminjaman</a>
    </div>
</div>

<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
    <div class="bg-white rounded shadow p-4"><div class="text-sm text-gray-500">Total Barang</div><div class="text-2xl font-bold">{{ $statistik['total_barang'] }}</div></div>
    <div class="bg-white rounded shadow p-4"><div class="text-sm text-gray-500">Tersedia</div><div class="text-2xl font-bold text-green-600">{{ $statistik['tersedia'] }}</div></div>
    <div class="bg-white rounded shadow p-4"><div class="text-sm text-gray-500">Sedang Dipinjam</div><div class="text-2xl font-bold text-blue-600">{{ $statistik['dipinjam'] }}</div></div>
    <div class="bg-white rounded shadow p-4"><div class="text-sm text-gray-500">Total Peminjaman</div><div class="text-2xl font-bold">{{ $statistik['total_peminjaman'] }}</div></div>
</div>

<h2 class="text-xl font-bold mb-3">Barang Terbaru</h2>
<div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
    @forelse($barangTerbaru as $b)
        <a href="{{ route('barang.detail', $b->id) }}" class="bg-white rounded shadow p-3 hover:shadow-lg">
            <div class="font-semibold text-sm">{{ $b->nama }}</div>
            <div class="text-xs text-gray-500">{{ $b->category->nama ?? '-' }} • {{ $b->kode_aset }}</div>
            <div class="text-xs mt-1">Stok tersedia: <span class="font-bold">{{ $b->jumlah_tersedia }}/{{ $b->jumlah_total }}</span></div>
            <span class="inline-block mt-1 text-xs px-2 py-0.5 rounded {{ $b->status==='Tersedia' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">{{ $b->status }}</span>
        </a>
    @empty
        <p class="text-gray-500">Belum ada barang.</p>
    @endforelse
</div>
@endsection
