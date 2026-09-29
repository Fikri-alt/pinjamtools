@extends('layouts.app')
@section('title','Laporan - Admin')
@section('content')
<h1 class="text-xl font-bold mb-3">Laporan Peminjaman</h1>
<form method="GET" class="bg-white rounded shadow p-3 mb-3 flex flex-wrap gap-2 text-sm">
    <label>Dari <input type="date" name="dari" value="{{ request('dari') }}" class="border rounded px-2 py-1"></label>
    <label>Sampai <input type="date" name="sampai" value="{{ request('sampai') }}" class="border rounded px-2 py-1"></label>
    <select name="department_id" class="border rounded px-2 py-1">
        <option value="">-- Semua departemen --</option>
        @foreach($departments as $d)<option value="{{ $d->id }}" @selected(request('department_id')==$d->id)>{{ $d->nama }}</option>@endforeach
    </select>
    <select name="item_id" class="border rounded px-2 py-1">
        <option value="">-- Semua barang --</option>
        @foreach($items as $it)<option value="{{ $it->id }}" @selected(request('item_id')==$it->id)>{{ $it->nama }}</option>@endforeach
    </select>
    <select name="status" class="border rounded px-2 py-1">
        <option value="">-- Semua status --</option>
        @foreach(['diajukan','disetujui','ditolak','dibatalkan','dipinjam','terlambat','dikembalikan','dikembalikan_terlambat','hilang'] as $s)<option value="{{ $s }}" @selected(request('status')===$s)>{{ $s }}</option>@endforeach
    </select>
    <button class="bg-slate-900 text-white px-3 py-1 rounded">Filter</button>
    <a href="{{ route('admin.reports.csv', request()->query()) }}" class="bg-green-600 text-white px-3 py-1 rounded">Export CSV</a>
    <a href="{{ route('admin.reports.pdf', request()->query()) }}" target="_blank" class="bg-red-600 text-white px-3 py-1 rounded">Export PDF (Print)</a>
</form>
<div class="bg-white rounded shadow overflow-auto">
<table class="w-full text-sm">
    <thead><tr class="text-left text-gray-500 border-b"><th class="p-2">Kode</th><th>Peminjam</th><th>Dept</th><th>Pinjam</th><th>Rencana</th><th>Status</th><th>Barang</th></tr></thead>
    <tbody>
    @forelse($loans as $l)
        <tr class="border-b">
            <td class="p-2 font-mono">{{ $l->kode_pinjam }}</td>
            <td class="p-2">{{ $l->nama_peminjam }}</td>
            <td class="p-2">{{ $l->department->nama ?? '-' }}</td>
            <td class="p-2">{{ optional($l->tgl_pinjam)->format('d/m/Y') }}</td>
            <td class="p-2">{{ optional($l->tgl_rencana_kembali)->format('d/m/Y') }}</td>
            <td class="p-2">{{ $l->statusLabel() }}</td>
            <td class="p-2">{{ $l->loanItems->map(fn($li)=>($li->item->nama??'#'.$li->item_id).' ×'.$li->jumlah)->join(', ') }}</td>
        </tr>
    @empty
        <tr><td colspan="7" class="p-3 text-gray-500">Tidak ada data.</td></tr>
    @endforelse
    </tbody>
</table>
</div>
<div class="mt-3">{{ $loans->links() }}</div>
@endsection
