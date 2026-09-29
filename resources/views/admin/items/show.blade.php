@extends('layouts.app')
@section('title','Riwayat '.$item->nama)
@section('content')
<h1 class="text-xl font-bold mb-1">{{ $item->nama }} <span class="font-mono text-sm text-gray-500">{{ $item->kode_aset }}</span></h1>
<p class="text-sm text-gray-500 mb-3">Total {{ $item->jumlah_total }} / Tersedia {{ $item->jumlah_tersedia }} - {{ $item->kondisi }} / {{ $item->status }}</p>
<div class="bg-white rounded shadow overflow-auto">
<table class="w-full text-sm">
    <thead><tr class="text-left text-gray-500 border-b"><th class="p-2">Kode</th><th>Peminjam</th><th>Tgl Pinjam</th><th>Jumlah</th><th>Status</th></tr></thead>
    <tbody>
    @forelse($riwayat as $l)
        <tr class="border-b">
            <td class="p-2 font-mono"><a href="{{ route('admin.loans.show',$l->id) }}" class="text-blue-600 underline">{{ $l->kode_pinjam }}</a></td>
            <td class="p-2">{{ $l->nama_peminjam }}</td>
            <td class="p-2">{{ optional($l->tgl_pinjam)->format('d/m/Y') }}</td>
            <td class="p-2">{{ $l->loanItems->first()->jumlah ?? '-' }}</td>
            <td class="p-2">{{ $l->statusLabel() }}</td>
        </tr>
    @empty
        <tr><td colspan="5" class="p-3 text-gray-500">Belum pernah dipinjam.</td></tr>
    @endforelse
    </tbody>
</table>
</div>
<div class="mt-3">{{ $riwayat->links() }}</div>
@endsection
