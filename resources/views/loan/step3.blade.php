@extends('layouts.app')
@section('title','Pengajuan Step 3 - Tujuan')
@section('content')
<h1 class="text-2xl font-bold mb-1">Pengajuan Peminjaman - Step 3: Tujuan & Persetujuan</h1>
<p class="text-sm text-gray-500 mb-4">Langkah 3 dari 3</p>
<div class="grid md:grid-cols-2 gap-4">
    <div class="bg-white rounded shadow p-4 text-sm">
        <h2 class="font-bold mb-2">Ringkasan</h2>
        <p><b>Nama:</b> {{ $s1['nama'] }} ({{ $s1['email'] }}, {{ $s1['no_hp'] }})</p>
        <p><b>Periode:</b> {{ $s2['tgl_pinjam'] }} s/d {{ $s2['tgl_rencana_kembali'] }}</p>
        <ul class="list-disc ml-5 mt-2">
            @foreach($s2['item_ids'] as $id)
                <li>{{ $items[$id]->nama ?? 'Item #'.$id }} × {{ $s2['jumlah'][$id] ?? 1 }}</li>
            @endforeach
        </ul>
    </div>
    <div class="bg-white rounded shadow p-4">
        <form method="POST" action="{{ route('pinjam.step3.post') }}">
            @csrf
            <label class="block mb-3 text-sm">Tujuan Peminjaman (min 10 karakter)
                <textarea name="tujuan" required minlength="10" rows="4" class="mt-1 w-full border rounded px-3 py-2">{{ old('tujuan', $data['tujuan'] ?? '') }}</textarea>
            </label>
            <label class="flex items-center gap-2 text-sm mb-3">
                <input type="checkbox" name="is_long_term" value="1" {{ old('is_long_term', $data['is_long_term'] ?? false) ? 'checked' : '' }}>
                Peminjaman jangka panjang / long term (&gt; 90 hari)
            </label>
            <label class="flex items-start gap-2 text-sm mb-3 bg-yellow-50 border border-yellow-300 rounded px-3 py-2">
                <input type="checkbox" name="langsung_ambil" value="1" {{ old('langsung_ambil', $data['langsung_ambil'] ?? false) ? 'checked' : '' }} class="mt-1">
                <span><b>Barang saya ambil langsung</b>. Jika dicentang, pengajuan ini <b>langsung berstatus DIPINJAM</b> dan stok langsung dikurangi.</span>
            </label>
            <label class="flex items-start gap-2 text-sm mb-4">
                <input type="checkbox" name="setuju_syarat" value="1" required>
                <span>Saya menyetujui syarat & ketentuan: mengembalikan tepat waktu, menjaga kondisi barang, dan bersedia dikenakan sanksi bila terlambat/rusak/hilang.</span>
            </label>
            <div class="flex gap-3">
                <a href="{{ route('pinjam.step2') }}" class="px-4 py-2 border rounded">&larr; Kembali</a>
                <button class="bg-green-700 text-white px-4 py-2 rounded">Kirim Pengajuan</button>
            </div>
        </form>
    </div>
</div>
@endsection
