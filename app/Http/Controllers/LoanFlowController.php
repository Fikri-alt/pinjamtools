<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Department;
use App\Models\Item;
use App\Models\Loan;
use App\Models\LoanItem;
use App\Models\NotificationLog;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class LoanFlowController extends Controller
{
    /* ============ KERANJANG (session) ============ */

    public function keranjang(): View
    {
        $cart = session()->get('cart', []);
        $items = Item::whereIn('id', array_keys($cart))->get()->keyBy('id');

        return view('loan.keranjang', compact('cart', 'items'));
    }

    public function tambahKeranjang(Request $request, int $id): RedirectResponse
    {
        $request->validate(['jumlah' => ['nullable', 'integer', 'min:1', 'max:100']]);

        $item = Item::findOrFail($id);
        $jumlah = (int) ($request->input('jumlah', 1));

        $cart = session()->get('cart', []);
        $cart[$id] = ($cart[$id] ?? 0) + $jumlah;
        session()->put('cart', $cart);

        return back()->with('success', $item->nama . ' ditambahkan ke keranjang.');
    }

    public function hapusKeranjang(int $id): RedirectResponse
    {
        $cart = session()->get('cart', []);
        unset($cart[$id]);
        session()->put('cart', $cart);

        return back()->with('success', 'Barang dihapus dari keranjang.');
    }

    /* ============ STEP 1: DATA DIRI ============ */

    public function step1(): View|RedirectResponse
    {
        $departments = Department::orderBy('nama')->get();
        $data = session()->get('pinjam.step1', []);
        $user = Auth::user();

        // Prefill dari user login bila ada
        if ($user && empty($data)) {
            $data = [
                'nama' => $user->name,
                'email' => $user->email,
                'no_hp' => $user->no_hp,
                'department_id' => $user->department_id,
            ];
        }

        return view('loan.step1', compact('departments', 'data'));
    }

    public function storeStep1(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:150'],
            'no_hp' => ['required', 'regex:/^08[0-9]{8,13}$/', 'max:25'],
            'department_id' => ['required', 'exists:departments,id'],
        ], [
            'no_hp.regex' => 'Nomor HP harus format Indonesia (diawali 08, total 10-15 digit).',
        ]);

        session()->put('pinjam.step1', $validated);

        return redirect()->route('pinjam.step2');
    }

    /* ============ STEP 2: BARANG + TANGGAL ============ */

    public function step2(): View|RedirectResponse
    {
        if (! session()->has('pinjam.step1')) {
            return redirect()->route('pinjam.step1')->with('error', 'Lengkapi data diri terlebih dahulu.');
        }

        $items = Item::with('category')->orderBy('nama')->get();
        $cart = session()->get('cart', []);
        $data = session()->get('pinjam.step2', []);

        return view('loan.step2', compact('items', 'cart', 'data'));
    }

    public function storeStep2(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'item_ids' => ['required', 'array', 'min:1'],
            'item_ids.*' => ['required', 'exists:items,id'],
            'jumlah' => ['required', 'array'],
            'tgl_pinjam' => ['required', 'date', 'after_or_equal:today'],
            'tgl_rencana_kembali' => ['required', 'date', 'after_or_equal:tgl_pinjam'],
        ], [
            'item_ids.required' => 'Pilih minimal 1 barang.',
            'tgl_rencana_kembali.after_or_equal' => 'Tanggal kembali harus >= tanggal pinjam.',
        ]);

        // Pasangkan jumlah berdasarkan ID barang (input bernama jumlah[<id>]),
        // sehingga urutan/centang tidak bisa tertukar.
        $pairs = [];
        foreach ($validated['item_ids'] as $itemId) {
            $qty = $validated['jumlah'][$itemId] ?? null;
            if ($qty === null || ! is_numeric($qty) || (int) $qty < 1 || (int) $qty > 1000) {
                return back()->withErrors(['jumlah' => 'Jumlah untuk barang terpilih tidak valid (1-1000).'])->withInput();
            }
            $pairs[$itemId] = (int) $qty;
        }

        $tglPinjam = Carbon::parse($validated['tgl_pinjam']);
        $tglKembali = Carbon::parse($validated['tgl_rencana_kembali']);
        $durasi = $tglPinjam->diffInDays($tglKembali);

        // Cek is_long_term sementara dari session step3 (atau default false).
        // Aturan: maks 90 hari kecuali long_term. Karena step3 belum diisi,
        // di step2 tolak > 90 hari dengan pesan agar centang long term di step3,
        // namun tetap simpan jika user nanti mencentang. Strategi: izinkan simpan,
        // validasi final di storeStep3. Di sini hanya beri warning via error jika >90
        // dan belum ada flag long_term di session.
        $isLongTerm = (bool) (session()->get('pinjam.step3.is_long_term', false));
        if ($durasi > 90 && ! $isLongTerm) {
            // Tetap simpan, tapi ingatkan - validasi keras dilakukan di step3.
            // Jika ingin keras di step2 juga, uncomment baris berikut:
            // return back()->withErrors(['tgl_rencana_kembali' => 'Maksimal 90 hari kecuali peminjaman jangka panjang (long term).'])->withInput();
        }

        // CEK BENTROK per item
        foreach ($pairs as $itemId => $minta) {
            $item = Item::findOrFail($itemId);

            // Total jumlah dipinjam pada rentang overlapping oleh loan aktif
            $terpakai = Loan::whereHas('loanItems', fn ($q) => $q->where('item_id', $itemId))
                ->whereIn('status', ['disetujui', 'dipinjam', 'terlambat'])
                ->where('tgl_pinjam', '<=', $tglKembali->toDateString())
                ->where('tgl_rencana_kembali', '>=', $tglPinjam->toDateString())
                ->withSum(['loanItems as total' => fn ($q) => $q->where('item_id', $itemId)], 'jumlah')
                ->get()
                ->sum('total');

            $sisa = $item->jumlah_total - (int) $terpakai;

            if ($minta > $sisa) {
                return back()
                    ->withErrors(["jumlah.{$itemId}" => "Stok {$item->nama} tidak cukup untuk rentang tanggal tersebut. Sisa: {$sisa}, diminta: {$minta}."])
                    ->withInput();
            }
        }

        session()->put('pinjam.step2', [
            'item_ids' => array_keys($pairs),
            'jumlah' => $pairs,
            'tgl_pinjam' => $validated['tgl_pinjam'],
            'tgl_rencana_kembali' => $validated['tgl_rencana_kembali'],
        ]);

        return redirect()->route('pinjam.step3');
    }

    /* ============ STEP 3: TUJUAN + LONG TERM + PERSETUJUAN ============ */

    public function step3(): View|RedirectResponse
    {
        if (! session()->has('pinjam.step1') || ! session()->has('pinjam.step2')) {
            return redirect()->route('pinjam.step1')->with('error', 'Lengkapi langkah sebelumnya terlebih dahulu.');
        }

        $s1 = session()->get('pinjam.step1');
        $s2 = session()->get('pinjam.step2');
        $data = session()->get('pinjam.step3', []);

        $items = Item::whereIn('id', $s2['item_ids'])->get()->keyBy('id');

        return view('loan.step3', compact('s1', 's2', 'items', 'data'));
    }

    public function storeStep3(Request $request): RedirectResponse
    {
        if (! session()->has('pinjam.step1') || ! session()->has('pinjam.step2')) {
            return redirect()->route('pinjam.step1')->with('error', 'Lengkapi langkah sebelumnya terlebih dahulu.');
        }

        $validated = $request->validate([
            'tujuan' => ['required', 'string', 'min:10', 'max:2000'],
            'is_long_term' => ['nullable', 'boolean'],
            'setuju_syarat' => ['required', 'accepted'],
            'langsung_ambil' => ['nullable', 'boolean'],
        ], [
            'setuju_syarat.accepted' => 'Anda harus menyetujui syarat & ketentuan.',
        ]);

        $langsungAmbil = (bool) ($validated['langsung_ambil'] ?? false);

        $s1 = session()->get('pinjam.step1');
        $s2 = session()->get('pinjam.step2');

        $isLongTerm = (bool) ($validated['is_long_term'] ?? false);

        $tglPinjam = Carbon::parse($s2['tgl_pinjam']);
        $tglKembali = Carbon::parse($s2['tgl_rencana_kembali']);
        $durasi = $tglPinjam->diffInDays($tglKembali);

        if ($durasi > 90 && ! $isLongTerm) {
            return back()->withErrors(['is_long_term' => 'Durasi > 90 hari wajib mencentang peminjaman jangka panjang (long term).'])->withInput();
        }

        // CEK BENTROK ulang (final, anti race-condition sederhana)
        foreach ($s2['item_ids'] as $itemId) {
            $item = Item::findOrFail($itemId);
            $minta = (int) ($s2['jumlah'][$itemId] ?? 0);
            if ($minta < 1) {
                return redirect()->route('pinjam.step2')
                    ->withErrors(['jumlah' => "Jumlah untuk {$item->nama} tidak valid. Ulangi langkah barang."]);
            }

            $terpakai = Loan::whereHas('loanItems', fn ($q) => $q->where('item_id', $itemId))
                ->whereIn('status', ['disetujui', 'dipinjam', 'terlambat'])
                ->where('tgl_pinjam', '<=', $tglKembali->toDateString())
                ->where('tgl_rencana_kembali', '>=', $tglPinjam->toDateString())
                ->withSum(['loanItems as total' => fn ($q) => $q->where('item_id', $itemId)], 'jumlah')
                ->get()
                ->sum('total');

            $sisa = $item->jumlah_total - (int) $terpakai;

            if ($minta > $sisa) {
                return redirect()->route('pinjam.step2')
                    ->withErrors(["jumlah.{$itemId}" => "Stok {$item->nama} habis untuk rentang tanggal tersebut (sisa {$sisa})."]);
            }
        }

        // Generate kode_pinjam PJM-YYYYMMDD-XXXX (unik)
        $kode = $this->generateKode();

        $loan = DB::transaction(function () use ($s1, $s2, $validated, $kode, $isLongTerm, $langsungAmbil, $request) {
            $loan = Loan::create([
                'kode_pinjam' => $kode,
                'user_id' => Auth::id(),
                'nama_peminjam' => $s1['nama'],
                'email' => $s1['email'],
                'no_hp' => $s1['no_hp'],
                'department_id' => $s1['department_id'],
                'tgl_pinjam' => $s2['tgl_pinjam'],
                'tgl_rencana_kembali' => $s2['tgl_rencana_kembali'],
                'tujuan' => $validated['tujuan'],
                'status' => $langsungAmbil ? 'dipinjam' : 'diajukan',
                'is_long_term' => $isLongTerm,
                'is_walkin' => false,
                'handover_kondisi' => $langsungAmbil ? 'Baik' : null,
                'handover_catatan' => $langsungAmbil ? 'Serah terima langsung oleh peminjam (centang ambil langsung, tanpa proses admin).' : null,
                'handover_at' => $langsungAmbil ? now() : null,
            ]);

            foreach ($s2['item_ids'] as $itemId) {
                LoanItem::create([
                    'loan_id' => $loan->id,
                    'item_id' => $itemId,
                    'jumlah' => (int) $s2['jumlah'][$itemId],
                    'kondisi_keluar' => $langsungAmbil ? 'Baik' : null,
                ]);

                // Jika ambil langsung: stok langsung dikurangi seperti handover admin.
                if ($langsungAmbil) {
                    $item = Item::find($itemId);
                    if ($item) {
                        $item->jumlah_tersedia = max(0, $item->jumlah_tersedia - (int) $s2['jumlah'][$itemId]);
                        if ($item->jumlah_tersedia <= 0) {
                            $item->status = 'Dipinjam';
                        }
                        $item->save();
                    }
                }
            }

            // JANGAN kurangi stok saat diajukan (dikurangi saat disetujui/handover),
            // kecuali mode ambil langsung di atas.

            NotificationLog::create([
                'tipe' => $langsungAmbil ? 'handover_langsung' : 'pengajuan_baru',
                'penerima' => $s1['email'],
                'channel' => 'email',
                'loan_id' => $loan->id,
                'pesan' => $langsungAmbil
                    ? "Serah terima langsung {$kode} oleh {$s1['nama']} ({$s1['email']}). Status: DIPINJAM."
                    : "Pengajuan baru {$kode} oleh {$s1['nama']} ({$s1['email']}).",
                'status_kirim' => 'antri',
            ]);

            AuditLog::create([
                'user_id' => Auth::id(),
                'aksi' => 'pengajuan_dibuat',
                'model_type' => Loan::class,
                'model_id' => $loan->id,
                'detail' => ['kode_pinjam' => $kode, 'items' => $s2['item_ids'], 'langsung_ambil' => $langsungAmbil],
                'ip' => $request->ip(),
            ]);

            return $loan;
        });

        // Kosongkan session peminjaman + keranjang
        session()->forget(['pinjam', 'cart']);

        return redirect()->route('pinjam.sukses', $loan->kode_pinjam)
            ->with('success', $langsungAmbil
                ? 'Serah terima langsung tercatat (' . $loan->kode_pinjam . '). Status: DIPINJAM. Kembalikan tepat waktu ya.'
                : 'Pengajuan berhasil dikirim dengan kode ' . $loan->kode_pinjam);
    }

    public function sukses(string $kode): View
    {
        $loan = Loan::with(['loanItems.item', 'department'])->where('kode_pinjam', $kode)->firstOrFail();

        return view('loan.sukses', compact('loan'));
    }

    /* ============ CEK STATUS ============ */

    public function cekStatus(): View
    {
        return view('loan.cek');
    }

    public function cariStatus(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'kode' => ['required', 'string', 'max:30'],
        ]);

        $kode = trim($validated['kode']);

        if (! Loan::where('kode_pinjam', $kode)->exists()) {
            return back()->withErrors(['kode' => 'Kode peminjaman tidak ditemukan.'])->withInput();
        }

        return redirect()->route('status.show', $kode);
    }

    public function showStatus(string $kode): View
    {
        $loan = Loan::with(['loanItems.item', 'department', 'user'])
            ->where('kode_pinjam', $kode)
            ->firstOrFail();

        return view('loan.timeline', compact('loan'));
    }

    /* ============ HELPER ============ */

    private function generateKode(): string
    {
        $prefix = 'PJM-' . now()->format('Ymd') . '-';

        do {
            $kode = $prefix . str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);
        } while (Loan::where('kode_pinjam', $kode)->exists());

        return $kode;
    }
}
