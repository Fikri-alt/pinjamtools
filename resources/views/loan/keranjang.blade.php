@extends('layouts.app')
@section('title','Keranjang - PinjamTools')
@section('content')
<h1 class="text-2xl font-bold mb-4">Keranjang</h1>
<div class="bg-white rounded shadow p-4">
    @if(empty($cart))
        <p class="text-gray-500">Keranjang kosong. <a href="{{ route('katalog') }}" class="text-blue-600 underline">Lihat katalog</a></p>
    @else
        <table class="w-full text-sm">
            <thead><tr class="border-b text-left text-gray-500"><th class="py-2">Barang</th><th>Jumlah</th><th>Aksi</th></tr></thead>
            <tbody>
            @foreach($cart as $id => $qty)
                <tr class="border-b">
                    <td class="py-2">{{ $items[$id]->nama ?? 'Item #'.$id }}</td>
                    <td>{{ $qty }}</td>
                    <td>
                        <form method="POST" action="{{ route('keranjang.hapus', $id) }}" class="inline">
                            @csrf @method('DELETE')
                            <button class="text-red-600 underline text-sm">Hapus</button>
                        </form>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
        <a href="{{ route('pinjam.step1') }}" class="inline-block mt-4 bg-slate-900 text-white px-4 py-2 rounded">Lanjut ke Pengajuan &rarr;</a>
    @endif
</div>
@endsection
