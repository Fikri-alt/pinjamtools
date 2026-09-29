@extends('layouts.app')
@section('title','Riwayat - Admin')
@section('content')
<h1 class="text-xl font-bold mb-3">Riwayat Peminjaman</h1>
<form method="GET" class="bg-white rounded shadow p-3 mb-3 flex flex-wrap gap-2 text-sm">
    <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari kode / nama" class="border rounded px-3 py-1">
    <select name="status" class="border rounded px-3 py-1">
        <option value="">-- Semua riwayat --</option>
        @foreach(['dikembalikan','dikembalikan_terlambat','ditolak','dibatalkan','hilang','terlambat'] as $s)
            <option value="{{ $s }}" @selected(request('status')===$s)>{{ $s }}</option>
        @endforeach
    </select>
    <button class="bg-slate-900 text-white px-3 py-1 rounded">Filter</button>
</form>
<div class="bg-white rounded shadow overflow-auto">
<table class="w-full text-sm">
    <thead><tr class="text-left text-gray-500 border-b"><th class="p-2">Kode</th><th>Peminjam</th><th>Barang</th><th>Kembali Aktual</th><th>Status</th><th>Aksi</th></tr></thead>
    <tbody>
    @forelse($loans as $l)
        <tr class="border-b">
            <td class="p-2 font-mono">{{ $l->kode_pinjam }}</td>
            <td class="p-2">{{ $l->nama_peminjam }}</td>
            <td class="p-2">{{ $l->loanItems->map(fn($li)=>($li->item->nama??'#'.$li->item_id).' ×'.$li->jumlah)->join(', ') }}</td>
            <td class="p-2">{{ optional($l->tgl_kembali_aktual)->format('d/m/Y') ?? '-' }}</td>
            <td class="p-2"><span class="px-2 py-0.5 rounded text-xs font-bold {{ in_array($l->status,['terlambat','dikembalikan_terlambat','hilang'])?'bg-red-100 text-red-700':'bg-gray-100 text-gray-700' }}">{{ $l->statusLabel() }}</span></td>
            <td class="p-2"><a href="{{ route('admin.loans.show',$l->id) }}" class="text-blue-600 underline">Detail</a></td>
        </tr>
    @empty
        <tr><td colspan="6" class="p-3 text-gray-500">Tidak ada data.</td></tr>
    @endforelse
    </tbody>
</table>
</div>
<div class="mt-3">{{ $loans->links() }}</div>
@endsection
