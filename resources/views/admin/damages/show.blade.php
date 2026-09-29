@extends('layouts.app')
@section('title','Kerusakan #'.$damage->id)
@section('content')
<h1 class="text-xl font-bold mb-3">Kerusakan #{{ $damage->id }} - {{ $damage->item->nama ?? '' }}</h1>
<div class="grid md:grid-cols-2 gap-4">
    <div class="bg-white rounded shadow p-4 text-sm">
        <p>Barang: {{ $damage->item->nama ?? '#'.$damage->item_id }}</p>
        <p>Loan: {{ $damage->loan->kode_pinjam ?? '-' }}</p>
        <p>Status: <b>{{ $damage->status }}</b></p>
        <p>Biaya: {{ $damage->biaya ? number_format($damage->biaya,0,',','.') : '-' }}</p>
        <p>Deskripsi: {{ $damage->deskripsi }}</p>
        <p>Tindak lanjut: {{ $damage->tindak_lanjut ?? '-' }}</p>
    </div>
    <form method="POST" action="{{ route('admin.damages.tindak',$damage->id) }}" class="bg-white rounded shadow p-4 text-sm space-y-3">
        @csrf
        <label class="block">Status
            <select name="status" class="border rounded px-3 py-1 w-full">
                @foreach(['Dilaporkan','Diproses','Selesai'] as $s)<option @selected($damage->status===$s)>{{ $s }}</option>@endforeach
            </select>
        </label>
        <label class="block">Biaya <input type="number" name="biaya" value="{{ old('biaya',$damage->biaya) }}" min="0" step="0.01" class="border rounded px-3 py-1 w-full"></label>
        <label class="block">Tindak lanjut <textarea name="tindak_lanjut" class="border rounded px-3 py-1 w-full">{{ old('tindak_lanjut',$damage->tindak_lanjut) }}</textarea></label>
        <button class="bg-slate-900 text-white px-4 py-2 rounded">Simpan (Selesai → item Baik/Tersedia + stok)</button>
    </form>
</div>
@endsection
