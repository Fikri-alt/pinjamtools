<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Item;
use App\Models\Loan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ItemController extends Controller
{
    public function index(Request $request): View
    {
        $query = Item::with('category')->orderBy('nama');

        if ($request->filled('q')) {
            $q = '%'.$request->string('q').'%';
            $query->where(function ($w) use ($q) {
                $w->where('nama', 'like', $q)->orWhere('kode_aset', 'like', $q);
            });
        }

        $items = $query->paginate(15)->withQueryString();

        return view('admin.items.index', compact('items'));
    }

    public function create(): View
    {
        $categories = Category::orderBy('nama')->get();

        return view('admin.items.form', ['item' => new Item(), 'categories' => $categories]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->rules($request);

        if ($request->hasFile('foto')) {
            $validated['foto'] = $request->file('foto')->store('items');
        }

        Item::create($validated);

        return redirect()->route('admin.items.index')->with('success', 'Barang ditambahkan.');
    }

    public function show(int $id): View
    {
        $item = Item::with('category')->findOrFail($id);
        $riwayat = Loan::with(['loanItems' => fn ($q) => $q->where('item_id', $id)])
            ->whereHas('loanItems', fn ($q) => $q->where('item_id', $id))
            ->orderByDesc('tgl_pinjam')
            ->paginate(15);

        return view('admin.items.show', compact('item', 'riwayat'));
    }

    public function edit(int $id): View
    {
        $item = Item::findOrFail($id);
        $categories = Category::orderBy('nama')->get();

        return view('admin.items.form', compact('item', 'categories'));
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $item = Item::findOrFail($id);
        $validated = $this->rules($request, $item->id);

        if ($request->hasFile('foto')) {
            if ($item->foto) {
                Storage::delete($item->foto);
            }
            $validated['foto'] = $request->file('foto')->store('items');
        }

        $item->update($validated);

        return redirect()->route('admin.items.index')->with('success', 'Barang diperbarui.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $item = Item::findOrFail($id);

        $aktif = Loan::whereHas('loanItems', fn ($q) => $q->where('item_id', $id))
            ->whereIn('status', ['diajukan', 'disetujui', 'dipinjam', 'terlambat'])
            ->exists();

        if ($aktif) {
            return back()->with('error', 'Barang tidak bisa dihapus karena masih ada peminjaman aktif.');
        }

        if ($item->foto) {
            Storage::delete($item->foto);
        }
        $item->delete();

        return redirect()->route('admin.items.index')->with('success', 'Barang dihapus.');
    }

    private function rules(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'kode_aset' => ['required', 'string', 'max:50', 'unique:items,kode_aset'.($ignoreId ? ",{$ignoreId}" : '')],
            'nama' => ['required', 'string', 'max:150'],
            'category_id' => ['required', 'exists:categories,id'],
            'merk' => ['nullable', 'string', 'max:100'],
            'foto' => ['nullable', 'image', 'max:2048'],
            'jumlah_total' => ['required', 'integer', 'min:1', 'max:100000'],
            'jumlah_tersedia' => ['required', 'integer', 'min:0', 'lte:jumlah_total'],
            'kondisi' => ['required', 'in:Baik,Rusak,Perbaikan'],
            'status' => ['required', 'in:Tersedia,Dipinjam,Perbaikan'],
            'deskripsi' => ['nullable', 'string', 'max:2000'],
        ]);
    }
}
