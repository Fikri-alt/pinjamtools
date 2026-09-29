@extends('layouts.app')
@section('title','Walk-in - Admin')
@section('content')
<h1 class="text-xl font-bold mb-3">Walk-in (Create + Approve + Handover Sekaligus)</h1>
<form method="POST" action="{{ route('admin.loans.walkin.store') }}" enctype="multipart/form-data" class="bg-white rounded shadow p-4 text-sm space-y-3">
    @csrf
    <div class="grid md:grid-cols-2 gap-3">
        <label>Nama <input type="text" name="nama_peminjam" class="border rounded px-3 py-1 w-full" required></label>
        <label>Email <input type="email" name="email" class="border rounded px-3 py-1 w-full" required></label>
        <label>No HP <input type="text" name="no_hp" placeholder="08..." class="border rounded px-3 py-1 w-full" required></label>
        <label>Departemen <select name="department_id" class="border rounded px-3 py-1 w-full" required>@foreach($departments as $d)<option value="{{ $d->id }}">{{ $d->nama }}</option>@endforeach</select></label>
        <label>Tgl pinjam <input type="date" name="tgl_pinjam" value="{{ date('Y-m-d') }}" class="border rounded px-3 py-1 w-full" required></label>
        <label>Rencana kembali <input type="date" name="tgl_rencana_kembali" value="{{ date('Y-m-d') }}" class="border rounded px-3 py-1 w-full" required></label>
    </div>
    <div>
        <p class="font-semibold mb-1">Barang (centang + jumlah)</p>
        @foreach($items as $it)
        <label class="flex items-center gap-2 border-b py-1">
            <input type="checkbox" name="item_ids[]" value="{{ $it->id }}">
            <span class="flex-1">{{ $it->nama }} (tersedia: {{ $it->jumlah_tersedia }})</span>
            <input type="number" name="jumlah[{{ $it->id }}]" value="1" min="1" class="border rounded px-2 py-0.5 w-20">
        </label>
        @endforeach
    </div>
    <label class="block">Tujuan <textarea name="tujuan" class="border rounded px-3 py-1 w-full" required></textarea></label>
    <label class="block">Catatan handover <textarea name="catatan" class="border rounded px-3 py-1 w-full"></textarea></label>
    <label class="block">Foto <input type="file" name="foto" accept="image/*" class="block mt-1"></label>
    <button class="bg-purple-600 text-white px-4 py-2 rounded">Buat Walk-in</button>
</form>
@endsection
