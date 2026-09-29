@extends('layouts.app')
@section('title','Katalog - PinjamTools')
@section('content')
<h1 class="text-2xl font-bold mb-4">Katalog Barang</h1>

@php
    $cartCount = count($cart ?? []);
    $cartQty = array_sum($cart ?? []);
@endphp
<div class="bg-white rounded shadow px-4 py-3 mb-4 flex flex-wrap items-center justify-between gap-2 text-sm">
    <div>
        <x-icon name="cart" /> Keranjang: <b>{{ $cartCount }}</b> jenis, <b>{{ $cartQty }}</b> barang
        @if($cartCount > 0)
            <span class="text-gray-500">-</span>
            @foreach($items as $b)
                @if(isset($cart[$b->id]))
                    <span class="inline-block bg-green-100 text-green-700 px-2 py-0.5 rounded text-xs font-semibold ml-1">{{ $b->nama }} ×{{ $cart[$b->id] }}</span>
                @endif
            @endforeach
        @endif
    </div>
    <div class="flex gap-2">
        <a href="{{ route('keranjang') }}" class="bg-slate-900 text-white px-4 py-2 rounded hover:bg-slate-700 font-semibold">Cek Keranjang ({{ $cartQty }})</a>
        @if($cartCount > 0)
            <a href="{{ route('pinjam.step1') }}" class="bg-yellow-500 px-4 py-2 rounded hover:bg-yellow-400 font-semibold">Lanjut Pengajuan &rarr;</a>
        @endif
    </div>
</div>
<form method="GET" action="{{ route('katalog') }}" class="bg-white rounded shadow p-4 mb-4 flex flex-wrap gap-3 items-end">
    <div>
        <label class="text-sm text-gray-600">Cari</label>
        <input type="text" name="q" value="{{ request('q') }}" placeholder="nama / kode / merk" class="block border rounded px-3 py-2 w-56">
    </div>
    <div>
        <label class="text-sm text-gray-600">Kategori</label>
        <select name="category_id" class="block border rounded px-3 py-2">
            <option value="">Semua</option>
            @foreach($categories as $c)
                <option value="{{ $c->id }}" {{ request('category_id')==$c->id?'selected':'' }}>{{ $c->nama }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="text-sm text-gray-600">Status</label>
        <select name="status" class="block border rounded px-3 py-2">
            <option value="">Semua</option>
            @foreach(['Tersedia','Dipinjam','Perbaikan'] as $s)
                <option value="{{ $s }}" {{ request('status')==$s?'selected':'' }}>{{ $s }}</option>
            @endforeach
        </select>
    </div>
    <button class="bg-slate-900 text-white px-4 py-2 rounded">Filter</button>
    <a href="{{ route('katalog') }}" class="text-sm text-gray-500 underline">Reset</a>
</form>

<div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
    @forelse($items as $b)
        <div class="bg-white rounded shadow p-4">
            <a href="{{ route('barang.detail', $b->id) }}" class="font-semibold hover:text-blue-600">{{ $b->nama }}</a>
            <div class="text-xs text-gray-500">{{ $b->category->nama ?? '-' }} • {{ $b->kode_aset }} • {{ $b->merk }}</div>
            <div class="text-sm mt-1">Tersedia: <b>{{ $b->jumlah_tersedia }}/{{ $b->jumlah_total }}</b> - {{ $b->status }}</div>
            @if(isset($cart[$b->id]))
                <div class="mt-1 text-xs font-semibold text-green-700"><x-icon name="check" /> Di keranjang ×{{ $cart[$b->id] }} - <a href="{{ route('keranjang') }}" class="underline">cek</a></div>
            @endif
            <form method="POST" action="{{ route('keranjang.tambah', $b->id) }}" class="mt-2 flex gap-2">
                @csrf
                <input type="number" name="jumlah" value="1" min="1" class="w-16 border rounded px-2 py-1 text-sm">
                <button class="bg-yellow-500 px-3 py-1 rounded text-sm font-semibold">+ Keranjang</button>
            </form>
        </div>
    @empty
        <p class="text-gray-500">Tidak ada barang ditemukan.</p>
    @endforelse
</div>
<div class="mt-4">{{ $items->links() }}</div>
@endsection
