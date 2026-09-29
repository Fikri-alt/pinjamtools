<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\Loan;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $today = Carbon::today();

        // Kartu ringkasan
        $dipinjam = Loan::whereIn('status', ['disetujui', 'dipinjam'])->count();
        $jatuhTempoHariIni = Loan::whereIn('status', ['disetujui', 'dipinjam', 'terlambat'])
            ->whereDate('tgl_rencana_kembali', $today)
            ->count();
        $terlambat = Loan::where('status', 'terlambat')->count()
            + Loan::whereIn('status', ['disetujui', 'dipinjam'])
                ->where('tgl_rencana_kembali', '<', $today->toDateString())
                ->count();
        $menunggu = Loan::where('status', 'diajukan')->count();
        $rusakPerbaikan = Item::whereIn('kondisi', ['Rusak', 'Perbaikan'])->count()
            + Item::where('status', 'Perbaikan')->count();

        // Tren per bulan 6 bulan terakhir
        $trenLabels = [];
        $trenData = [];
        for ($i = 5; $i >= 0; $i--) {
            $bulan = now()->subMonths($i);
            $trenLabels[] = $bulan->translatedFormat('M Y') ?: $bulan->format('M Y');
            $trenData[] = Loan::whereYear('tgl_pinjam', $bulan->year)
                ->whereMonth('tgl_pinjam', $bulan->month)
                ->count();
        }

        // Per departemen
        $perDepartemen = Loan::select('departments.nama as nama', DB::raw('COUNT(loans.id) as total'))
            ->leftJoin('departments', 'departments.id', '=', 'loans.department_id')
            ->groupBy('departments.nama')
            ->orderByDesc('total')
            ->get();

        // Top 10 barang paling sering dipinjam
        $topBarang = DB::table('loan_items')
            ->join('items', 'items.id', '=', 'loan_items.item_id')
            ->select('items.nama', DB::raw('SUM(loan_items.jumlah) as total'))
            ->groupBy('items.nama')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        // Tabel aktif dengan sisa hari
        $aktif = Loan::with(['loanItems.item', 'department'])
            ->whereIn('status', ['disetujui', 'dipinjam', 'terlambat'])
            ->orderBy('tgl_rencana_kembali')
            ->take(20)
            ->get()
            ->map(function ($loan) use ($today) {
                $rencana = Carbon::parse($loan->tgl_rencana_kembali);
                $sisaHari = $today->diffInDays($rencana, false); // negatif = terlambat
                $loan->sisa_hari = (int) $sisaHari;
                $loan->warna = $sisaHari < 0 ? 'red' : ($sisaHari === 0 ? 'yellow' : ($sisaHari <= 3 ? 'orange' : 'green'));

                return $loan;
            });

        // Komposisi kondisi kembali (dari loan_items.kondisi_kembali)
        $komposisiKembali = DB::table('loan_items')
            ->select('kondisi_kembali', DB::raw('COUNT(*) as total'))
            ->whereNotNull('kondisi_kembali')
            ->groupBy('kondisi_kembali')
            ->get();

        return view('admin.dashboard', compact(
            'dipinjam',
            'jatuhTempoHariIni',
            'terlambat',
            'menunggu',
            'rusakPerbaikan',
            'trenLabels',
            'trenData',
            'perDepartemen',
            'topBarang',
            'aktif',
            'komposisiKembali'
        ));
    }
}
