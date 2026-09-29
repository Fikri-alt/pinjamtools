@extends('layouts.app')
@section('title','Dashboard Admin - PinjamTools')
@section('content')
<h1 class="text-2xl font-bold mb-4">Dashboard Admin</h1>

<div class="flex flex-wrap gap-2 mb-4 text-sm">
    <a href="{{ route('admin.pengajuan') }}" class="bg-slate-900 text-white px-4 py-2 rounded hover:bg-slate-700 font-semibold"><x-icon name="clipboard" /> Antrean Persetujuan ({{ $menunggu }})</a>
    <a href="{{ route('admin.loans.aktif') }}" class="bg-white border px-4 py-2 rounded hover:bg-gray-100">Peminjaman Aktif</a>
    @if(in_array(Auth::user()->role, ['admin', 'super_admin'], true))
    <a href="{{ route('admin.loans.walkin') }}" class="bg-white border px-4 py-2 rounded hover:bg-gray-100">+ Walk-in</a>
    @endif
</div>

<div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
    <div class="bg-white rounded shadow p-4"><div class="text-sm text-gray-500">Dipinjam</div><div class="text-2xl font-bold text-blue-600">{{ $dipinjam }}</div></div>
    <div class="bg-white rounded shadow p-4"><div class="text-sm text-gray-500">Jatuh Tempo Hari Ini</div><div class="text-2xl font-bold text-yellow-600">{{ $jatuhTempoHariIni }}</div></div>
    <div class="bg-white rounded shadow p-4"><div class="text-sm text-gray-500">Terlambat</div><div class="text-2xl font-bold text-red-600">{{ $terlambat }}</div></div>
    <a href="{{ route('admin.pengajuan') }}" class="bg-white rounded shadow p-4 hover:ring-2 hover:ring-slate-900"><div class="text-sm text-gray-500">Menunggu Persetujuan</div><div class="text-2xl font-bold">{{ $menunggu }}</div><div class="text-xs text-blue-600 mt-1">Klik untuk proses →</div></a>
    <div class="bg-white rounded shadow p-4"><div class="text-sm text-gray-500">Rusak / Perbaikan</div><div class="text-2xl font-bold text-orange-600">{{ $rusakPerbaikan }}</div></div>
</div>

<div class="grid md:grid-cols-2 gap-4 mb-6">
    <div class="bg-white rounded shadow p-4">
        <h2 class="font-bold mb-2">Tren Peminjaman (6 bulan terakhir)</h2>
        <table class="w-full text-sm">
            @foreach($trenLabels as $i => $label)
                <tr class="border-b"><td class="py-1">{{ $label }}</td><td class="text-right font-bold">{{ $trenData[$i] }}</td></tr>
            @endforeach
        </table>
    </div>
    <div class="bg-white rounded shadow p-4">
        <h2 class="font-bold mb-2">Per Departemen</h2>
        <table class="w-full text-sm">
            @forelse($perDepartemen as $d)
                <tr class="border-b"><td class="py-1">{{ $d->nama ?? '(tanpa departemen)' }}</td><td class="text-right font-bold">{{ $d->total }}</td></tr>
            @empty
                <tr><td class="text-gray-500">Belum ada data.</td></tr>
            @endforelse
        </table>
        <h2 class="font-bold mt-4 mb-2">Top 10 Barang</h2>
        <table class="w-full text-sm">
            @forelse($topBarang as $t)
                <tr class="border-b"><td class="py-1">{{ $t->nama }}</td><td class="text-right font-bold">{{ $t->total }}</td></tr>
            @empty
                <tr><td class="text-gray-500">Belum ada data.</td></tr>
            @endforelse
        </table>
        <h2 class="font-bold mt-4 mb-2">Komposisi Kondisi Kembali</h2>
        <table class="w-full text-sm">
            @forelse($komposisiKembali as $k)
                <tr class="border-b"><td class="py-1">{{ $k->kondisi_kembali ?? '-' }}</td><td class="text-right font-bold">{{ $k->total }}</td></tr>
            @empty
                <tr><td class="text-gray-500">Belum ada data pengembalian.</td></tr>
            @endforelse
        </table>
    </div>
</div>

<div class="bg-white rounded shadow p-4">
    <h2 class="font-bold mb-2">Peminjaman Aktif (sisa hari + warna)</h2>
    <div class="overflow-auto">
    <table class="w-full text-sm">
        <thead><tr class="text-left text-gray-500 border-b"><th class="py-2">Kode</th><th>Peminjam</th><th>Barang</th><th>Rencana Kembali</th><th>Sisa Hari</th><th>Status</th></tr></thead>
        <tbody>
        @forelse($aktif as $l)
            <tr class="border-b">
                <td class="py-2 font-mono">{{ $l->kode_pinjam }}</td>
                <td>{{ $l->nama_peminjam }}</td>
                <td>{{ $l->loanItems->map(fn($li) => ($li->item->nama ?? '#'.$li->item_id).' ×'.$li->jumlah)->join(', ') }}</td>
                <td>{{ optional($l->tgl_rencana_kembali)->format('d/m/Y') }}</td>
                <td>
                    <span class="px-2 py-0.5 rounded text-xs font-bold
                        {{ $l->warna==='red' ? 'bg-red-100 text-red-700' : ($l->warna==='yellow' ? 'bg-yellow-100 text-yellow-700' : ($l->warna==='orange' ? 'bg-orange-100 text-orange-700' : 'bg-green-100 text-green-700')) }}">
                        {{ $l->sisa_hari }} hari
                    </span>
                </td>
                <td>{{ $l->statusLabel() }}</td>
            </tr>
        @empty
            <tr><td colspan="6" class="py-2 text-gray-500">Tidak ada peminjaman aktif.</td></tr>
        @endforelse
        </tbody>
    </table>
    </div>
</div>
@endsection
