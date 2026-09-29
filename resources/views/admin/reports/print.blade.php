<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Laporan Peminjaman (Print)</title>
<style>
body{font-family:Arial,sans-serif;font-size:12px;color:#111}
table{width:100%;border-collapse:collapse;margin-top:10px}
th,td{border:1px solid #333;padding:4px 6px;text-align:left}
.toolbar{margin:10px 0}
@media print{.toolbar{display:none}}
</style>
</head>
<body>
<h2>Laporan Peminjaman - PinjamTools</h2>
<p>Filter: {{ json_encode($filter) }} | Dicetak: {{ now()->format('d/m/Y H:i') }} | Total: {{ $loans->count() }}</p>
<div class="toolbar"><button onclick="window.print()"><x-icon name="printer" /> Print / Simpan PDF</button> <a href="{{ route('admin.reports.index', $filter) }}">Kembali</a></div>
<table>
<thead><tr><th>Kode</th><th>Peminjam</th><th>Dept</th><th>Pinjam</th><th>Rencana</th><th>Aktual</th><th>Status</th><th>Barang</th></tr></thead>
<tbody>
@foreach($loans as $l)
<tr>
<td>{{ $l->kode_pinjam }}</td>
<td>{{ $l->nama_peminjam }}</td>
<td>{{ $l->department->nama ?? '-' }}</td>
<td>{{ optional($l->tgl_pinjam)->format('d/m/Y') }}</td>
<td>{{ optional($l->tgl_rencana_kembali)->format('d/m/Y') }}</td>
<td>{{ optional($l->tgl_kembali_aktual)->format('d/m/Y') ?? '-' }}</td>
<td>{{ $l->status }}</td>
<td>{{ $l->loanItems->map(fn($li)=>($li->item->nama??'#'.$li->item_id).' x'.$li->jumlah)->join('; ') }}</td>
</tr>
@endforeach
</tbody>
</table>
</body>
</html>
