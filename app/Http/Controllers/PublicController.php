<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Item;
use App\Models\Loan;
use App\Models\LoanItem;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PublicController extends Controller
{
    public function beranda(): View
    {
        $today = now()->toDateString();

        // Barang tersedia hari ini: status Tersedia & jumlah_tersedia > 0
        // dikurangi yang sedang dipinjam overlapping hari ini dihitung via loan aktif
        $tersediaHariIni = Item::tersedia()->count();

        $barangTerbaru = Item::with('category')
            ->latest()
            ->take(6)
            ->get();

        $statistik = [
            'total_barang' => Item::count(),
            'tersedia' => Item::tersedia()->count(),
            'dipinjam' => Loan::whereIn('status', ['disetujui', 'dipinjam', 'terlambat'])->count(),
            'total_peminjaman' => Loan::count(),
        ];

        $categories = Category::orderBy('nama')->get();

        return view('public.beranda', compact('tersediaHariIni', 'barangTerbaru', 'statistik', 'categories', 'today'));
    }

    public function katalog(Request $request): View
    {
        $query = Item::with('category')->latest();

        if ($request->filled('q')) {
            $q = $request->input('q');
            $query->where(function ($w) use ($q) {
                $w->where('nama', 'like', "%{$q}%")
                    ->orWhere('kode_aset', 'like', "%{$q}%")
                    ->orWhere('merk', 'like', "%{$q}%");
            });
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->input('category_id'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $items = $query->paginate(12)->withQueryString();
        $categories = Category::orderBy('nama')->get();
        $cart = session()->get('cart', []);

        return view('public.katalog', compact('items', 'categories', 'cart'));
    }

    public function detail(int $id): View
    {
        $item = Item::with('category')->findOrFail($id);

        // Riwayat pemakaian 10 terakhir
        $riwayat = LoanItem::with(['loan.department'])
            ->where('item_id', $item->id)
            ->whereHas('loan')
            ->latest()
            ->take(10)
            ->get();

        // Hitung sisa stok real-time (overlapping hari ini)
        $today = now()->toDateString();
        $dipinjamAktif = Loan::whereHas('loanItems', fn ($q) => $q->where('item_id', $item->id))
            ->whereIn('status', ['disetujui', 'dipinjam', 'terlambat'])
            ->where('tgl_pinjam', '<=', $today)
            ->where('tgl_rencana_kembali', '>=', $today)
            ->withSum(['loanItems as terjual' => fn ($q) => $q->where('item_id', $item->id)], 'jumlah')
            ->get()
            ->sum('terjual');

        $sisaReal = max(0, $item->jumlah_total - (int) $dipinjamAktif);

        return view('public.detail', compact('item', 'riwayat', 'sisaReal'));
    }
}
