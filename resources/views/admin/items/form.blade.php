@extends('layouts.app')
@section('title',($item->exists ? 'Edit' : 'Tambah').' Barang - Admin')
@section('content')
<h1 class="text-xl font-bold mb-3">{{ $item->exists ? 'Edit' : 'Tambah' }} Barang</h1>
<form method="POST" action="{{ $item->exists ? route('admin.items.update',$item->id) : route('admin.items.store') }}" enctype="multipart/form-data" class="bg-white rounded shadow p-4 text-sm space-y-3">
    @csrf
    @if($item->exists) @method('PUT') @endif
    <div class="grid md:grid-cols-2 gap-3">
        <label>Kode aset <input type="text" name="kode_aset" value="{{ old('kode_aset',$item->kode_aset) }}" class="border rounded px-3 py-1 w-full" required></label>
        <label>Nama <input type="text" name="nama" value="{{ old('nama',$item->nama) }}" class="border rounded px-3 py-1 w-full" required></label>
        <label>Kategori <select name="category_id" class="border rounded px-3 py-1 w-full" required>@foreach($categories as $c)<option value="{{ $c->id }}" @selected(old('category_id',$item->category_id)==$c->id)>{{ $c->nama }}</option>@endforeach</select></label>
        <label>Merk <input type="text" name="merk" value="{{ old('merk',$item->merk) }}" class="border rounded px-3 py-1 w-full"></label>
        <label>Jumlah total <input type="number" name="jumlah_total" value="{{ old('jumlah_total',$item->jumlah_total ?? 1) }}" min="1" class="border rounded px-3 py-1 w-full" required></label>
        <label>Jumlah tersedia <input type="number" name="jumlah_tersedia" value="{{ old('jumlah_tersedia',$item->jumlah_tersedia ?? 1) }}" min="0" class="border rounded px-3 py-1 w-full" required></label>
        <label>Kondisi <select name="kondisi" class="border rounded px-3 py-1 w-full">@foreach(['Baik','Rusak','Perbaikan'] as $k)<option @selected(old('kondisi',$item->kondisi)===$k)>{{ $k }}</option>@endforeach</select></label>
        <label>Status <select name="status" class="border rounded px-3 py-1 w-full">@foreach(['Tersedia','Dipinjam','Perbaikan'] as $s)<option @selected(old('status',$item->status)===$s)>{{ $s }}</option>@endforeach</select></label>
    </div>
    <label class="block">Deskripsi <textarea name="deskripsi" class="border rounded px-3 py-1 w-full">{{ old('deskripsi',$item->deskripsi) }}</textarea></label>
    <label class="block">Foto <input type="file" name="foto" accept="image/*" class="block mt-1"></label>
    @if($item->foto)<p><a href="{{ asset('storage/'.$item->foto) }}" target="_blank" class="text-blue-600 underline">Lihat foto saat ini</a></p>@endif
    <button class="bg-slate-900 text-white px-4 py-2 rounded">Simpan</button>
</form>
@endsection
