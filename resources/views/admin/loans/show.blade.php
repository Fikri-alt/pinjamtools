@extends('layouts.app')
@section('title','Detail '.$loan->kode_pinjam.' - Admin')
@section('content')
<h1 class="text-xl font-bold mb-1">Detail {{ $loan->kode_pinjam }} @if($loan->is_walkin)<span class="text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded">walk-in</span>@endif</h1>
<p class="text-sm text-gray-500 mb-3">{{ $loan->nama_peminjam }} - {{ $loan->statusLabel() }}</p>

<div class="grid md:grid-cols-2 gap-4 mb-4">
    <div class="bg-white rounded shadow p-4 text-sm">
        <h2 class="font-bold mb-2">Data Peminjam</h2>
        <p>Nama: {{ $loan->nama_peminjam }}</p>
        <p>Email: {{ $loan->email }}</p>
        <p>No HP: {{ $loan->no_hp }}</p>
        <p>Departemen: {{ $loan->department->nama ?? '-' }}</p>
        <p>Pinjam: {{ optional($loan->tgl_pinjam)->format('d/m/Y') }} → Rencana kembali: {{ optional($loan->tgl_rencana_kembali)->format('d/m/Y') }}</p>
        <p>Aktual kembali: {{ optional($loan->tgl_kembali_aktual)->format('d/m/Y') ?? '-' }}</p>
        <p>Tujuan: {{ $loan->tujuan }}</p>
        @if($loan->rejection_reason)<p class="mt-2 text-red-600">Alasan tolak: {{ $loan->rejection_reason }}</p>@endif
        @if($loan->handover_foto)<p class="mt-2"><a href="{{ Storage::url($loan->handover_foto) }}" target="_blank" class="text-blue-600 underline">Foto handover</a></p>@endif
        @if($loan->return_foto)<p><a href="{{ Storage::url($loan->return_foto) }}" target="_blank" class="text-blue-600 underline">Foto return</a></p>@endif
    </div>
    <div class="bg-white rounded shadow p-4 text-sm">
        <h2 class="font-bold mb-2">Barang</h2>
        <ul class="list-disc ml-5">
        @foreach($loan->loanItems as $li)
            <li>{{ $li->item->nama ?? '#'.$li->item_id }} ×{{ $li->jumlah }} (keluar: {{ $li->kondisi_keluar ?? '-' }} / kembali: {{ $li->kondisi_kembali ?? '-' }})</li>
        @endforeach
        </ul>
        <h2 class="font-bold mt-4 mb-2">Aksi</h2>
        @if(in_array(Auth::user()->role, ['admin', 'super_admin'], true))
        <div class="flex flex-wrap gap-2">
            @if($loan->status==='diajukan')
                <form method="POST" action="{{ route('admin.loans.approve',$loan->id) }}">@csrf<button class="bg-green-600 text-white px-3 py-1 rounded">Approve</button></form>
                <a href="{{ route('admin.loans.handover',$loan->id) }}" class="bg-blue-600 text-white px-3 py-1 rounded">Handover</a>
            @endif
            @if($loan->status==='disetujui')
                <a href="{{ route('admin.loans.handover',$loan->id) }}" class="bg-blue-600 text-white px-3 py-1 rounded">Handover</a>
            @endif
            @if(in_array($loan->status,['dipinjam','terlambat']))
                <a href="{{ route('admin.loans.return',$loan->id) }}" class="bg-emerald-600 text-white px-3 py-1 rounded">Return</a>
                <form method="POST" action="{{ route('admin.loans.hilang',$loan->id) }}" onsubmit="return confirm('Tandai hilang?')">@csrf<button class="bg-red-600 text-white px-3 py-1 rounded">Tandai Hilang</button></form>
            @endif
        </div>
        @else
        <p class="text-xs text-gray-500">Mode lihat saja (role supervisor).</p>
        @endif
        @if(in_array(Auth::user()->role, ['admin', 'super_admin'], true) && $loan->status==='diajukan')
        <form method="POST" action="{{ route('admin.loans.reject',$loan->id) }}" class="mt-3 flex gap-2">
            @csrf
            <input type="text" name="rejection_reason" placeholder="Alasan penolakan (wajib)" class="border rounded px-3 py-1 flex-1" required>
            <button class="bg-red-600 text-white px-3 py-1 rounded">Reject</button>
        </form>
        @endif
    </div>
</div>

@if($loan->extensions->count())
<div class="bg-white rounded shadow p-4 text-sm mb-4">
    <h2 class="font-bold mb-2">Perpanjangan</h2>
    <table class="w-full text-sm">
        @foreach($loan->extensions as $e)
        <tr class="border-b">
            <td class="py-1">{{ $e->tgl_lama }} → {{ $e->tgl_baru }} ({{ $e->status }}) - {{ $e->alasan }}</td>
            <td class="text-right">
                @if($e->status==='diajukan' && in_array(Auth::user()->role, ['admin', 'super_admin'], true))
                <form method="POST" action="{{ route('admin.extensions.keputusan',$e->id) }}" class="inline-flex gap-1">
                    @csrf
                    <button name="keputusan" value="disetujui" class="bg-green-600 text-white px-2 py-0.5 rounded text-xs">Setujui</button>
                    <button name="keputusan" value="ditolak" class="bg-red-600 text-white px-2 py-0.5 rounded text-xs">Tolak</button>
                </form>
                @endif
            </td>
        </tr>
        @endforeach
    </table>
</div>
@endif
@endsection
