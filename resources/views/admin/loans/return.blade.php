@extends('layouts.app')
@section('title','Return '.$loan->kode_pinjam)
@section('content')
<h1 class="text-xl font-bold mb-3">Pengembalian {{ $loan->kode_pinjam }}</h1>
<form method="POST" action="{{ route('admin.loans.return.store',$loan->id) }}" enctype="multipart/form-data" class="bg-white rounded shadow p-4 text-sm space-y-3">
    @csrf
    <label class="block">Tanggal kembali aktual <input type="date" name="tgl_kembali_aktual" value="{{ date('Y-m-d') }}" class="border rounded px-3 py-1" required></label>
    @foreach($loan->loanItems as $li)
    <div class="border rounded p-3">
        <p class="font-semibold">{{ $li->item->nama ?? '#'.$li->item_id }} ×{{ $li->jumlah }}</p>
        <label class="block mt-1">Kondisi kembali
            <select name="kondisi_kembali[{{ $li->id }}]" class="border rounded px-3 py-1">
                <option>Normal</option>
                <option>Tidak Lengkap</option>
                <option>Rusak</option>
            </select>
        </label>
    </div>
    @endforeach
    <label class="block">Catatan <textarea name="catatan" class="border rounded px-3 py-1 w-full"></textarea></label>
    <label class="block">Foto (opsional) <input type="file" name="foto" accept="image/*" class="block mt-1"></label>
    <button class="bg-emerald-600 text-white px-4 py-2 rounded">Simpan Pengembalian</button>
</form>
@endsection
