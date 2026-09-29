@extends('layouts.app')
@section('title','Kerusakan - Admin')
@section('content')
<h1 class="text-xl font-bold mb-3">Data Kerusakan</h1>
<form method="GET" class="bg-white rounded shadow p-3 mb-3 flex flex-wrap gap-2 text-sm">
    <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari deskripsi" class="border rounded px-3 py-1">
    <select name="status" class="border rounded px-3 py-1">
        <option value="">-- Semua --</option>
        @foreach(['Dilaporkan','Diproses','Selesai'] as $s)
            <option value="{{ $s }}" @selected(request('status')===$s)>{{ $s }}</option>
        @endforeach
    </select>
    <button class="bg-slate-900 text-white px-3 py-1 rounded">Filter</button>
</form>
<div class="bg-white rounded shadow overflow-auto">
<table class="w-full text-sm">
    <thead><tr class="text-left text-gray-500 border-b"><th class="p-2">ID</th><th>Barang</th><th>Loan</th><th>Deskripsi</th><th>Status</th><th>Biaya</th><th>Aksi</th></tr></thead>
    <tbody>
    @forelse($damages as $d)
        <tr class="border-b">
            <td class="p-2">{{ $d->id }}</td>
            <td class="p-2">{{ $d->item->nama ?? '#'.$d->item_id }}</td>
            <td class="p-2 font-mono">{{ $d->loan->kode_pinjam ?? '-' }}</td>
            <td class="p-2">{{ \Illuminate\Support\Str::limit($d->deskripsi,60) }}</td>
            <td class="p-2"><span class="px-2 py-0.5 rounded text-xs font-bold {{ $d->status==='Selesai'?'bg-green-100 text-green-700':($d->status==='Diproses'?'bg-yellow-100 text-yellow-700':'bg-red-100 text-red-700') }}">{{ $d->status }}</span></td>
            <td class="p-2">{{ $d->biaya ? number_format($d->biaya,0,',','.') : '-' }}</td>
            <td class="p-2 flex gap-2">
                <a href="{{ route('admin.damages.show',$d->id) }}" class="text-blue-600 underline">Proses</a>
                @if($d->status==='Selesai')
                <form method="POST" action="{{ route('admin.damages.destroy',$d->id) }}" onsubmit="return confirm('Hapus?')">@csrf @method('DELETE')<button class="text-red-600 underline">Hapus</button></form>
                @endif
            </td>
        </tr>
    @empty
        <tr><td colspan="7" class="p-3 text-gray-500">Tidak ada data.</td></tr>
    @endforelse
    </tbody>
</table>
</div>
<div class="mt-3">{{ $damages->links() }}</div>
@endsection
