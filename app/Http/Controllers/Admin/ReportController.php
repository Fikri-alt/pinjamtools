<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Department;
use App\Models\Item;
use App\Models\Loan;
use App\Models\NotificationLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    private function filteredQuery(Request $request)
    {
        $query = Loan::with(['loanItems.item', 'department', 'user'])
            ->orderBy('tgl_pinjam');

        if ($request->filled('dari')) {
            $query->where('tgl_pinjam', '>=', $request->string('dari'));
        }
        if ($request->filled('sampai')) {
            $query->where('tgl_pinjam', '<=', $request->string('sampai'));
        }
        if ($request->filled('department_id')) {
            $query->where('department_id', $request->integer('department_id'));
        }
        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }
        if ($request->filled('item_id')) {
            $query->whereHas('loanItems', fn ($q) => $q->where('item_id', $request->integer('item_id')));
        }

        return $query;
    }

    public function index(Request $request): View
    {
        $loans = $this->filteredQuery($request)->paginate(20)->withQueryString();
        $departments = Department::orderBy('nama')->get();
        $items = Item::orderBy('nama')->get();

        // Tandai notifikasi: catat audit + tulis notification log laporan dilihat
        if ($request->query()) {
            AuditLog::create([
                'user_id' => Auth::id(),
                'aksi' => 'laporan_dilihat',
                'model_type' => Loan::class,
                'model_id' => null,
                'detail' => $request->query(),
                'ip' => $request->ip(),
            ]);
        }

        return view('admin.reports.index', compact('loans', 'departments', 'items'));
    }

    public function exportCsv(Request $request): StreamedResponse
    {
        $loans = $this->filteredQuery($request)->with(['loanItems.item', 'department'])->get();

        $filename = 'laporan-peminjaman-'.now()->format('Ymd-His').'.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($loans) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['kode_pinjam', 'nama_peminjam', 'email', 'departemen', 'tgl_pinjam', 'tgl_rencana_kembali', 'tgl_kembali_aktual', 'status', 'barang', 'tujuan']);
            foreach ($loans as $l) {
                $barang = $l->loanItems->map(fn ($li) => ($li->item->nama ?? '#'.$li->item_id).' x'.$li->jumlah)->join('; ');
                fputcsv($out, [
                    $l->kode_pinjam,
                    $l->nama_peminjam,
                    $l->email,
                    $l->department->nama ?? '',
                    optional($l->tgl_pinjam)->format('Y-m-d'),
                    optional($l->tgl_rencana_kembali)->format('Y-m-d'),
                    optional($l->tgl_kembali_aktual)->format('Y-m-d'),
                    $l->status,
                    $barang,
                    $l->tujuan,
                ]);
            }
            fclose($out);
        };

        $this->kirimLog('export_csv', count($loans).' baris diekspor ke CSV.');

        return response()->stream($callback, 200, $headers);
    }

    /** Export PDF HTML printable (tanpa dompdf agar ringan). */
    public function exportPdf(Request $request): View
    {
        $loans = $this->filteredQuery($request)->with(['loanItems.item', 'department'])->get();
        $filter = $request->only(['dari', 'sampai', 'department_id', 'item_id', 'status']);

        $this->kirimLog('export_pdf', count($loans).' baris diekspor ke PDF-printable.');

        return view('admin.reports.print', compact('loans', 'filter'));
    }

    /** Tulis ke notifications + Log (tanpa SMTP sungguhan). */
    public function kirimLog(string $tipe, string $pesan): void
    {
        NotificationLog::create([
            'tipe' => 'laporan_'.$tipe,
            'penerima' => Auth::user()->email ?? 'admin',
            'channel' => 'email',
            'loan_id' => null,
            'pesan' => $pesan,
            'status_kirim' => 'terkirim',
            'sent_at' => now(),
        ]);

        Log::info("[LAPORAN][{$tipe}] {$pesan}");
    }
}
