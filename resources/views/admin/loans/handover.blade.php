@extends('layouts.app')
@section('title','Handover '.$loan->kode_pinjam)
@section('content')
<h1 class="text-xl font-bold mb-3">Handover {{ $loan->kode_pinjam }}</h1>
<form method="POST" action="{{ route('admin.loans.handover.store',$loan->id) }}" enctype="multipart/form-data" class="bg-white rounded shadow p-4 text-sm space-y-3">
    @csrf
    @foreach($loan->loanItems as $li)
    <div class="border rounded p-3">
        <p class="font-semibold">{{ $li->item->nama ?? '#'.$li->item_id }} ×{{ $li->jumlah }}</p>
        <label class="block mt-1">Kondisi keluar
            <input type="text" name="kondisi_keluar[{{ $li->id }}]" value="Baik" class="border rounded px-3 py-1 w-full" required>
        </label>
    </div>
    @endforeach
    <label class="block">Catatan <textarea name="catatan" class="border rounded px-3 py-1 w-full"></textarea></label>
    <label class="block">Foto (opsional) <input type="file" name="foto" accept="image/*" class="block mt-1"></label>
    <button class="bg-blue-600 text-white px-4 py-2 rounded">Simpan Handover → Dipinjam</button>
</form>
@endsection
