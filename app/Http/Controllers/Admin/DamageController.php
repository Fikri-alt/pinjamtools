<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DamageCase;
use App\Models\Item;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DamageController extends Controller
{
    public function index(Request $request): View
    {
        $query = DamageCase::with(['item', 'loan'])->orderByDesc('created_at');

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        if ($request->filled('q')) {
            $q = '%'.$request->string('q').'%';
            $query->where('deskripsi', 'like', $q);
        }

        $damages = $query->paginate(15)->withQueryString();

        return view('admin.damages.index', compact('damages'));
    }

    public function show(int $id): View
    {
        $damage = DamageCase::with(['item', 'loan'])->findOrFail($id);

        return view('admin.damages.show', compact('damage'));
    }

    public function tindakLanjut(Request $request, int $id): RedirectResponse
    {
        $damage = DamageCase::with('item')->findOrFail($id);

        $validated = $request->validate([
            'status' => ['required', 'in:Dilaporkan,Diproses,Selesai'],
            'biaya' => ['nullable', 'numeric', 'min:0', 'max:1000000000'],
            'tindak_lanjut' => ['nullable', 'string', 'max:2000'],
        ]);

        $damage->update($validated);

        // Jika Selesai: kembalikan item ke Baik/Tersedia + tambah stok tersedia
        if ($validated['status'] === 'Selesai' && $damage->item) {
            $item = $damage->item;
            $qty = 1;
            if ($damage->loan_id) {
                $li = $damage->loan->loanItems()->where('item_id', $item->id)->first();
                if ($li) {
                    $qty = (int) $li->jumlah;
                }
            }
            $item->update([
                'kondisi' => 'Baik',
                'status' => 'Tersedia',
                'jumlah_tersedia' => min($item->jumlah_total, $item->jumlah_tersedia + 0), // stok sudah dikembalikan saat return; pastikan tersedia
            ]);
            // Tambah stok tersedia hanya jika masih kurang dari total (aman dari double-count)
            if ($item->jumlah_tersedia < $item->jumlah_total) {
                $item->increment('jumlah_tersedia', min($qty, $item->jumlah_total - $item->jumlah_tersedia));
            }
            if ($item->jumlah_tersedia > 0) {
                $item->update(['status' => 'Tersedia', 'kondisi' => 'Baik']);
            }
        }

        return back()->with('success', 'Tindak lanjut disimpan.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $damage = DamageCase::findOrFail($id);

        if ($damage->status !== 'Selesai') {
            return back()->with('error', 'Hanya kasus berstatus Selesai yang boleh dihapus.');
        }

        $damage->delete();

        return redirect()->route('admin.damages.index')->with('success', 'Data kerusakan dihapus.');
    }
}
