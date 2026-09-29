@extends('layouts.app')
@section('title','Peminjaman Aktif - Admin')
@section('content')
<h1 class="text-xl font-bold mb-3">Peminjaman Aktif</h1>
<form method="GET" class="bg-white rounded shadow p-3 mb-3 flex gap-2 text-sm">
    <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari kode / nama" class="border rounded px-3 py-1">
    <button class="bg-slate-900 text-white px-3 py-1 rounded">Cari</button>
</form>
<p class="text-xs text-gray-500 mb-2">Badge: <span class="px-2 py-0.5 rounded bg-green-100 text-green-700 font-bold">hijau aman</span> <span class="px-2 py-0.5 rounded bg-yellow-100 text-yellow-700 font-bold">kuning ≤1 hari</span> <span class="px-2 py-0.5 rounded bg-red-100 text-red-700 font-bold">merah terlambat</span></p>
<div class="bg-white rounded shadow overflow-auto">
<table class="w-full text-sm">
    <thead><tr class="text-left text-gray-500 border-b"><th class="p-2">Kode</th><th>Peminjam</th><th>Barang</th><th>Rencana Kembali</th><th>Sisa</th><th>Status</th><th>Aksi</th></tr></thead>
    <tbody>
    @forelse($loans as $l)
        <tr class="border-b">
            <td class="p-2 font-mono">{{ $l->kode_pinjam }}</td>
            <td class="p-2">{{ $l->nama_peminjam }}</td>
            <td class="p-2">{{ $l->loanItems->map(fn($li)=>($li->item->nama??'#'.$li->item_id).' ×'.$li->jumlah)->join(', ') }}</td>
            <td class="p-2">{{ optional($l->tgl_rencana_kembali)->format('d/m/Y') }}</td>
            <td class="p-2"><span class="px-2 py-0.5 rounded text-xs font-bold {{ ($l->badge??'green')==='red'?'bg-red-100 text-red-700':(($l->badge??'green')==='yellow'?'bg-yellow-100 text-yellow-700':'bg-green-100 text-green-700') }}">{{ $l->sisa_hari }} hari</span></td>
            <td class="p-2">{{ $l->statusLabel() }}</td>
            <td class="p-2"><a href="{{ route('admin.loans.show',$l->id) }}" class="text-blue-600 underline">Detail</a></td>
        </tr>
    @empty
        <tr><td colspan="7" class="p-3 text-gray-500">Tidak ada peminjaman aktif.</td></tr>
    @endforelse
    </tbody>
</table>
</div>
<div class="mt-3">{{ $loans->links() }}</div>
@endsection
