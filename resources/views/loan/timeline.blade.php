@extends('layouts.app')
@section('title','Status '.$loan->kode_pinjam.' - PinjamTools')
@section('content')
<div class="bg-white rounded shadow p-6 mb-4">
    <h1 class="text-2xl font-bold font-mono">{{ $loan->kode_pinjam }}</h1>
    <p class="text-sm text-gray-500">Peminjam: {{ $loan->nama_peminjam }} ({{ $loan->email }}) • Dept: {{ $loan->department->nama ?? '-' }}</p>
    <p class="text-sm">Periode: {{ optional($loan->tgl_pinjam)->format('d/m/Y') }} - {{ optional($loan->tgl_rencana_kembali)->format('d/m/Y') }}
        @if($loan->is_long_term) <span class="text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded">Long term</span> @endif
    </p>
    <p class="mt-1">Status: <span class="font-bold px-2 py-0.5 rounded bg-blue-100 text-blue-700">{{ $loan->statusLabel() }}</span></p>
    <p class="text-sm mt-1">Tujuan: {{ $loan->tujuan }}</p>
    @if($loan->rejection_reason)
        <p class="text-sm text-red-600 mt-1">Alasan penolakan: {{ $loan->rejection_reason }}</p>
    @endif
    <h2 class="font-bold mt-4 mb-1">Barang:</h2>
    <ul class="list-disc ml-5 text-sm">
        @foreach($loan->loanItems as $li)
            <li>{{ $li->item->nama ?? 'Item #'.$li->item_id }} × {{ $li->jumlah }}</li>
        @endforeach
    </ul>
</div>

<div class="bg-white rounded shadow p-6">
    <h2 class="text-lg font-bold mb-3">Timeline</h2>
    @php
        $steps = ['diajukan','disetujui','dipinjam','dikembalikan'];
        $order = array_flip($steps);
        $terminal = ['ditolak','dibatalkan','hilang','terlambat','dikembalikan_terlambat'];
        $current = $loan->status;
    @endphp
    @if(in_array($current, $terminal))
        <div class="border-l-4 pl-4 space-y-3">
            <div><div class="font-semibold">Diajukan</div><div class="text-xs text-gray-500">{{ $loan->created_at->format('d/m/Y H:i') }}</div></div>
            <div><div class="font-semibold text-red-600">{{ $loan->statusLabel() }}</div><div class="text-xs text-gray-500">{{ $loan->updated_at->format('d/m/Y H:i') }}</div></div>
        </div>
    @else
        <ol class="border-l-4 border-gray-200 pl-4 space-y-3">
            @foreach($steps as $s)
                @php $done = isset($order[$current]) && $order[$s] <= $order[$current]; @endphp
                <li>
                    <div class="font-semibold {{ $done ? 'text-green-700' : 'text-gray-400' }}">@if($done)<x-icon name="check" /> @endif{{ ucfirst($s) }}</div>
                    @if($s==='diajukan')<div class="text-xs text-gray-500">{{ $loan->created_at->format('d/m/Y H:i') }}</div>@endif
                    @if($s!=='diajukan' && $done)<div class="text-xs text-gray-500">{{ $loan->updated_at->format('d/m/Y H:i') }}</div>@endif
                </li>
            @endforeach
        </ol>
    @endif
    <a href="{{ route('status.form') }}" class="inline-block mt-4 text-sm text-blue-600 underline">Cek kode lain</a>
</div>
@endsection
