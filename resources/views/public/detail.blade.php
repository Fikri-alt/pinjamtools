@extends('layouts.app')
@section('title','Detail Barang - PinjamTools')
@section('content')
<div class="bg-white rounded shadow p-6 mb-6">
    <a href="{{ route('katalog') }}" class="text-sm text-blue-600 underline">&larr; Kembali ke katalog</a>
    <h1 class="text-2xl font-bold mt-2">{{ $item->nama }}</h1>
    <div class="text-sm text-gray-500">{{ $item->kode_aset }} • {{ $item->category->nama ?? '-' }} • {{ $item->merk ?? '-' }}</div>
    <div class="mt-3 grid md:grid-cols-2 gap-4 text-sm">
        <div>
            <p><b>Kondisi:</b> {{ $item->kondisi }}</p>
            <p><b>Status:</b> {{ $item->status }}</p>
            <p><b>Stok total:</b> {{ $item->jumlah_total }}</p>
            <p><b>Stok tersedia (sistem):</b> {{ $item->jumlah_tersedia }}</p>
            <p><b>Sisa real-time hari ini:</b> {{ $sisaReal }}</p>
            <p class="mt-2"><b>Deskripsi:</b><br>{{ $item->deskripsi ?? '-' }}</p>
        </div>
        <div>
            <form method="POST" action="{{ route('keranjang.tambah', $item->id) }}" class="border rounded p-4">
                @csrf
                <label class="text-sm">Jumlah
                    <input type="number" name="jumlah" value="1" min="1" max="{{ $item->jumlah_total }}" class="block border rounded px-3 py-2 w-24 mt-1">
                </label>
                <button class="mt-3 bg-yellow-500 px-4 py-2 rounded font-semibold">+ Keranjang</button>
                <a href="{{ route('pinjam.step1') }}" class="ml-2 text-sm text-blue-600 underline">Langsung ajukan</a>
            </form>
        </div>
    </div>
</div>

<div class="bg-white rounded shadow p-6">
    <h2 class="text-lg font-bold mb-3">Riwayat Pemakaian (10 terakhir)</h2>
    <table class="w-full text-sm">
        <thead><tr class="border-b text-left text-gray-500"><th class="py-2">Kode</th><th>Jumlah</th><th>Periode</th><th>Status</th></tr></thead>
        <tbody>
        @forelse($riwayat as $r)
            <tr class="border-b">
                <td class="py-2">{{ $r->loan->kode_pinjam ?? '-' }}</td>
                <td>{{ $r->jumlah }}</td>
                <td>{{ optional($r->loan->tgl_pinjam)->format('d/m/Y') }} - {{ optional($r->loan->tgl_rencana_kembali)->format('d/m/Y') }}</td>
                <td>{{ $r->loan->statusLabel() ?? '-' }}</td>
            </tr>
        @empty
            <tr><td colspan="4" class="py-2 text-gray-500">Belum ada riwayat.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
@endsection
