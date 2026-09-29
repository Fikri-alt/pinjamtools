@extends('layouts.app')
@section('title','Pengajuan Step 2 - Barang & Tanggal')
@section('content')
<h1 class="text-2xl font-bold mb-1">Pengajuan Peminjaman - Step 2: Barang & Tanggal</h1>
<p class="text-sm text-gray-500 mb-4">Langkah 2 dari 3 - tanggal kembali ≥ tanggal pinjam, maks 90 hari kecuali long term. Stok dicek overlapping.</p>
<div class="bg-white rounded shadow p-6">
    <form method="POST" action="{{ route('pinjam.step2.post') }}">
        @csrf
        <div class="grid md:grid-cols-2 gap-4 mb-4">
            <label class="text-sm">Tanggal Pinjam
                <input type="date" name="tgl_pinjam" value="{{ old('tgl_pinjam', $data['tgl_pinjam'] ?? date('Y-m-d')) }}" required class="mt-1 w-full border rounded px-3 py-2">
            </label>
            <label class="text-sm">Tanggal Rencana Kembali
                <input type="date" name="tgl_rencana_kembali" value="{{ old('tgl_rencana_kembali', $data['tgl_rencana_kembali'] ?? '') }}" required class="mt-1 w-full border rounded px-3 py-2">
            </label>
        </div>
        <h2 class="font-bold mb-2">Pilih Barang</h2>
        @php
            $oldIds = old('item_ids', $data['item_ids'] ?? array_keys($cart ?? []));
            $rawQty = old('jumlah', $data['jumlah'] ?? []);
            // Normalisasi jumlah menjadi map [item_id => qty] (dukung data sesi lama berbentuk list sejajar)
            $qtyById = [];
            if (is_array($rawQty)) {
                if (array_is_list($rawQty) && isset($data['item_ids']) && is_array($data['item_ids'])) {
                    foreach ($data['item_ids'] as $k => $oid) $qtyById[$oid] = $rawQty[$k] ?? 1;
                } else {
                    $qtyById = $rawQty;
                }
            }
            foreach (($cart ?? []) as $cid => $cq) { if (!isset($qtyById[$cid])) $qtyById[$cid] = $cq; }
        @endphp
        <div class="grid md:grid-cols-2 gap-2 max-h-96 overflow-auto border rounded p-3">
            @foreach($items as $i => $b)
                @php
                    $pos = is_array($oldIds) ? array_search($b->id, $oldIds) : false;
                    $checked = $pos !== false;
                    $qty = $qtyById[$b->id] ?? 1;
                @endphp
                <label class="flex items-center gap-2 border rounded px-2 py-1 text-sm">
                    <input type="checkbox" name="item_ids[]" value="{{ $b->id }}" {{ $checked?'checked':'' }}>
                    <span class="flex-1">{{ $b->nama }} <span class="text-gray-500">({{ $b->kode_aset }}, tersedia {{ $b->jumlah_tersedia }}/{{ $b->jumlah_total }})</span></span>
                    <input type="number" name="jumlah[{{ $b->id }}]" value="{{ $qty }}" min="1" class="w-16 border rounded px-1 py-0.5">
                </label>
            @endforeach
        </div>
        <p class="text-xs text-gray-500 mt-2">Catatan: jumlah terikat langsung pada tiap barang (berdasarkan ID), jadi centang/tidak centang tidak akan tertukar. Sistem memvalidasi stok overlapping per item.</p>
        <div class="mt-4 flex gap-3">
            <a href="{{ route('pinjam.step1') }}" class="px-4 py-2 border rounded">&larr; Kembali</a>
            <button class="bg-slate-900 text-white px-4 py-2 rounded">Lanjut &rarr;</button>
        </div>
    </form>
</div>
@endsection
