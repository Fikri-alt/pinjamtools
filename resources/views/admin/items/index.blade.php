@extends('layouts.app')
@section('title','Inventaris - Admin')
@section('content')
<div class="flex items-center justify-between mb-3">
    <h1 class="text-xl font-bold">Inventaris</h1>
    <a href="{{ route('admin.items.create') }}" class="bg-slate-900 text-white px-3 py-1 rounded text-sm">+ Tambah Barang</a>
</div>
<form method="GET" class="bg-white rounded shadow p-3 mb-3 flex gap-2 text-sm">
    <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari nama / kode aset" class="border rounded px-3 py-1">
    <button class="bg-slate-900 text-white px-3 py-1 rounded">Cari</button>
</form>
<div class="bg-white rounded shadow overflow-auto">
<table class="w-full text-sm">
    <thead><tr class="text-left text-gray-500 border-b"><th class="p-2">Kode</th><th>Nama</th><th>Kategori</th><th>Total/Tersedia</th><th>Kondisi</th><th>Status</th><th>Aksi</th></tr></thead>
    <tbody>
    @forelse($items as $it)
        <tr class="border-b">
            <td class="p-2 font-mono">{{ $it->kode_aset }}</td>
            <td class="p-2">{{ $it->nama }}</td>
            <td class="p-2">{{ $it->category->nama ?? '-' }}</td>
            <td class="p-2">{{ $it->jumlah_total }} / {{ $it->jumlah_tersedia }}</td>
            <td class="p-2">{{ $it->kondisi }}</td>
            <td class="p-2"><span class="px-2 py-0.5 rounded text-xs font-bold {{ $it->status==='Tersedia'?'bg-green-100 text-green-700':($it->status==='Dipinjam'?'bg-yellow-100 text-yellow-700':'bg-red-100 text-red-700') }}">{{ $it->status }}</span></td>
            <td class="p-2 flex gap-2">
                <a href="{{ route('admin.items.show',$it->id) }}" class="text-blue-600 underline">Riwayat</a>
                <a href="{{ route('admin.items.edit',$it->id) }}" class="text-emerald-600 underline">Edit</a>
                <form method="POST" action="{{ route('admin.items.destroy',$it->id) }}" onsubmit="return confirm('Hapus?')">@csrf @method('DELETE')<button class="text-red-600 underline">Hapus</button></form>
            </td>
        </tr>
    @empty
        <tr><td colspan="7" class="p-3 text-gray-500">Belum ada barang.</td></tr>
    @endforelse
    </tbody>
</table>
</div>
<div class="mt-3">{{ $items->links() }}</div>
@endsection
