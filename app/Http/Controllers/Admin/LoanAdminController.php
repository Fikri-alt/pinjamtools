<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Department;
use App\Models\Item;
use App\Models\Loan;
use App\Models\LoanExtension;
use App\Models\DamageCase;
use App\Models\NotificationLog;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class LoanAdminController extends Controller
{
    /* ============ ANTREAN / AKTIF / RIWAYAT ============ */

    public function antrean(Request $request): View
    {
        $query = Loan::with(['loanItems.item', 'department', 'approver'])
            ->orderByDesc('created_at');

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        } else {
            $query->whereIn('status', ['diajukan', 'disetujui']);
        }

        if ($request->filled('q')) {
            $q = '%'.$request->string('q').'%';
            $query->where(function ($w) use ($q) {
                $w->where('kode_pinjam', 'like', $q)
                    ->orWhere('nama_peminjam', 'like', $q);
            });
        }

        $loans = $query->paginate(15)->withQueryString();

        return view('admin.loans.antrean', compact('loans'));
    }

    public function aktif(Request $request): View
    {
        $today = Carbon::today();
        $query = Loan::with(['loanItems.item', 'department'])
            ->whereIn('status', ['disetujui', 'dipinjam', 'terlambat'])
            ->orderBy('tgl_rencana_kembali');

        if ($request->filled('q')) {
            $q = '%'.$request->string('q').'%';
            $query->where(function ($w) use ($q) {
                $w->where('kode_pinjam', 'like', $q)
                    ->orWhere('nama_peminjam', 'like', $q);
            });
        }

        $loans = $query->paginate(15)->withQueryString();
        $loans->getCollection()->transform(function ($loan) use ($today) {
            $rencana = Carbon::parse($loan->tgl_rencana_kembali);
            $sisa = (int) $today->diffInDays($rencana, false);
            $loan->sisa_hari = $sisa;
            $loan->badge = $sisa < 0 ? 'red' : ($sisa <= 1 ? 'yellow' : 'green');

            return $loan;
        });

        return view('admin.loans.aktif', compact('loans'));
    }

    public function riwayat(Request $request): View
    {
        $query = Loan::with(['loanItems.item', 'department'])
            ->orderByDesc('tgl_kembali_aktual')
            ->orderByDesc('updated_at');

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        } else {
            $query->whereIn('status', ['dikembalikan', 'dikembalikan_terlambat', 'ditolak', 'dibatalkan', 'hilang', 'terlambat']);
        }

        if ($request->filled('q')) {
            $q = '%'.$request->string('q').'%';
            $query->where(function ($w) use ($q) {
                $w->where('kode_pinjam', 'like', $q)
                    ->orWhere('nama_peminjam', 'like', $q);
            });
        }

        $loans = $query->paginate(15)->withQueryString();

        return view('admin.loans.riwayat', compact('loans'));
    }

    public function show(int $id): View
    {
        $loan = Loan::with(['loanItems.item', 'department', 'user', 'approver', 'extensions.approver'])->findOrFail($id);

        return view('admin.loans.show', compact('loan'));
    }

    /* ============ APPROVE / REJECT ============ */

    public function approve(Request $request, int $id): RedirectResponse
    {
        $loan = Loan::with('loanItems')->findOrFail($id);

        if ($loan->status !== 'diajukan') {
            return back()->with('error', 'Hanya pengajuan berstatus diajukan yang bisa disetujui.');
        }

        // Cek bentrok ulang per item
        foreach ($loan->loanItems as $li) {
            $terpakai = $this->stokTerpakai($li->item_id, $loan->tgl_pinjam, $loan->tgl_rencana_kembali, $loan->id);
            $item = Item::find($li->item_id);
            $sisa = $item ? ($item->jumlah_total - $terpakai) : 0;
            if ($li->jumlah > $sisa) {
                return back()->with('error', "Stok {$item->nama} tidak cukup (sisa {$sisa}, diminta {$li->jumlah}).");
            }
        }

        $loan->update([
            'status' => 'disetujui',
            'approved_by' => Auth::id(),
        ]);

        $this->kirimNotifikasi($loan, 'persetujuan', "Pengajuan {$loan->kode_pinjam} telah DISETUJUI. Silakan ambil barang sesuai jadwal.");
        $this->audit('loan_disetujui', $loan, ['kode' => $loan->kode_pinjam]);

        return redirect()->route('admin.loans.show', $loan->id)->with('success', 'Pengajuan disetujui.');
    }

    public function reject(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'rejection_reason' => ['required', 'string', 'min:5', 'max:2000'],
        ], ['rejection_reason.required' => 'Alasan penolakan wajib diisi.']);

        $loan = Loan::findOrFail($id);

        if ($loan->status !== 'diajukan') {
            return back()->with('error', 'Hanya pengajuan berstatus diajukan yang bisa ditolak.');
        }

        $loan->update([
            'status' => 'ditolak',
            'rejection_reason' => $validated['rejection_reason'],
            'approved_by' => Auth::id(),
        ]);

        $this->kirimNotifikasi($loan, 'penolakan', "Pengajuan {$loan->kode_pinjam} DITOLAK. Alasan: {$validated['rejection_reason']}");
        $this->audit('loan_ditolak', $loan, ['alasan' => $validated['rejection_reason']]);

        return redirect()->route('admin.loans.show', $loan->id)->with('success', 'Pengajuan ditolak.');
    }

    /* ============ HANDOVER ============ */

    public function handoverForm(int $id): View
    {
        $loan = Loan::with(['loanItems.item', 'department'])->findOrFail($id);
        abort_if(! in_array($loan->status, ['disetujui', 'diajukan']), 400, 'Handover hanya untuk status diajukan/disetujui.');

        return view('admin.loans.handover', compact('loan'));
    }

    public function handoverStore(Request $request, int $id): RedirectResponse
    {
        $loan = Loan::with('loanItems.item')->findOrFail($id);
        abort_if(! in_array($loan->status, ['disetujui', 'diajukan']), 400, 'Status tidak valid untuk handover.');

        $validated = $request->validate([
            'kondisi_keluar' => ['required', 'array'],
            'kondisi_keluar.*' => ['required', 'string', 'max:50'],
            'catatan' => ['nullable', 'string', 'max:2000'],
            'foto' => ['nullable', 'image', 'max:2048'],
        ]);

        // Cek bentrok ulang sebelum serah terima
        foreach ($loan->loanItems as $li) {
            $terpakai = $this->stokTerpakai($li->item_id, $loan->tgl_pinjam, $loan->tgl_rencana_kembali, $loan->id);
            $item = Item::find($li->item_id);
            $sisa = $item ? ($item->jumlah_total - $terpakai) : 0;
            if ($li->jumlah > $sisa) {
                return back()->with('error', "Stok {$item->nama} tidak cukup untuk handover (sisa {$sisa}).")->withInput();
            }
        }

        $fotoPath = $loan->handover_foto;
        if ($request->hasFile('foto')) {
            $fotoPath = $request->file('foto')->store('handover');
        }

        DB::transaction(function () use ($loan, $validated, $fotoPath) {
            foreach ($loan->loanItems as $li) {
                $li->update(['kondisi_keluar' => $validated['kondisi_keluar'][$li->id] ?? 'Baik']);

                $item = $li->item;
                if ($item) {
                    $item->jumlah_tersedia = max(0, $item->jumlah_tersedia - $li->jumlah);
                    if ($item->jumlah_tersedia <= 0) {
                        $item->status = 'Dipinjam';
                    }
                    $item->save();
                }
            }

            $loan->update([
                'status' => 'dipinjam',
                'approved_by' => $loan->approved_by ?? Auth::id(),
                'handover_catatan' => $validated['catatan'] ?? null,
                'handover_foto' => $fotoPath,
                'handover_at' => now(),
            ]);
        });

        $this->kirimNotifikasi($loan->fresh(), 'handover', "Barang {$loan->kode_pinjam} telah diserahterimakan. Status: DIPINJAM.");
        $this->audit('loan_handover', $loan, ['foto' => $fotoPath]);

        return redirect()->route('admin.loans.show', $loan->id)->with('success', 'Handover berhasil. Status menjadi dipinjam.');
    }

    /* ============ RETURN ============ */

    public function returnForm(int $id): View
    {
        $loan = Loan::with(['loanItems.item'])->findOrFail($id);
        abort_if(! in_array($loan->status, ['dipinjam', 'terlambat']), 400, 'Return hanya untuk status dipinjam/terlambat.');

        return view('admin.loans.return', compact('loan'));
    }

    public function returnStore(Request $request, int $id): RedirectResponse
    {
        $loan = Loan::with('loanItems.item')->findOrFail($id);
        abort_if(! in_array($loan->status, ['dipinjam', 'terlambat']), 400, 'Status tidak valid untuk return.');

        $validated = $request->validate([
            'tgl_kembali_aktual' => ['required', 'date'],
            'kondisi_kembali' => ['required', 'array'],
            'kondisi_kembali.*' => ['required', 'in:Normal,Tidak Lengkap,Rusak'],
            'catatan' => ['nullable', 'string', 'max:2000'],
            'foto' => ['nullable', 'image', 'max:2048'],
        ]);

        $fotoPath = $loan->return_foto;
        if ($request->hasFile('foto')) {
            $fotoPath = $request->file('foto')->store('returns');
        }

        $terlambat = Carbon::parse($validated['tgl_kembali_aktual'])->gt(Carbon::parse($loan->tgl_rencana_kembali));

        DB::transaction(function () use ($loan, $validated, $fotoPath, $terlambat) {
            foreach ($loan->loanItems as $li) {
                $kondisi = $validated['kondisi_kembali'][$li->id] ?? 'Normal';
                $li->update(['kondisi_kembali' => $kondisi]);

                $item = $li->item;
                if ($item) {
                    // Kembalikan stok (capped)
                    $item->jumlah_tersedia = min($item->jumlah_total, $item->jumlah_tersedia + $li->jumlah);

                    if (in_array($kondisi, ['Rusak', 'Tidak Lengkap'], true)) {
                        DamageCase::create([
                            'item_id' => $item->id,
                            'loan_id' => $loan->id,
                            'deskripsi' => "Otomatis dari pengembalian {$loan->kode_pinjam}: {$item->nama} x{$li->jumlah} kondisi {$kondisi}.",
                            'status' => 'Dilaporkan',
                        ]);
                        $item->kondisi = 'Perbaikan';
                        $item->status = 'Perbaikan';
                    } elseif ($item->jumlah_tersedia > 0 && $item->status !== 'Perbaikan') {
                        $item->status = 'Tersedia';
                    }
                    $item->save();
                }
            }

            $loan->update([
                'tgl_kembali_aktual' => $validated['tgl_kembali_aktual'],
                'return_catatan' => $validated['catatan'] ?? null,
                'return_foto' => $fotoPath,
                'status' => $terlambat ? 'dikembalikan_terlambat' : 'dikembalikan',
            ]);
        });

        $this->kirimNotifikasi($loan->fresh(), 'pengembalian', "Barang {$loan->kode_pinjam} telah dikembalikan".($terlambat ? ' TERLAMBAT.' : '.'));
        $this->audit('loan_return', $loan, ['terlambat' => $terlambat]);

        return redirect()->route('admin.loans.show', $loan->id)->with('success', 'Pengembalian dicatat.');
    }

    /* ============ WALK-IN ============ */

    public function walkinForm(): View
    {
        $departments = Department::orderBy('nama')->get();
        $items = Item::orderBy('nama')->get();

        return view('admin.loans.walkin', compact('departments', 'items'));
    }

    public function walkinStore(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama_peminjam' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:150'],
            'no_hp' => ['required', 'regex:/^08[0-9]{8,13}$/', 'max:25'],
            'department_id' => ['required', 'exists:departments,id'],
            'item_ids' => ['required', 'array', 'min:1'],
            'item_ids.*' => ['required', 'exists:items,id'],
            'jumlah' => ['required', 'array', 'min:1'],
            'jumlah.*' => ['required', 'integer', 'min:1', 'max:1000'],
            'tgl_pinjam' => ['required', 'date'],
            'tgl_rencana_kembali' => ['required', 'date', 'after_or_equal:tgl_pinjam'],
            'tujuan' => ['required', 'string', 'min:5', 'max:2000'],
            'catatan' => ['nullable', 'string', 'max:2000'],
            'foto' => ['nullable', 'image', 'max:2048'],
        ]);

        // Normalisasi: dukung jumlah asosiatif jumlah[item_id] maupun paralel
        $qtyMap = [];
        foreach ($validated['item_ids'] as $idx => $itemId) {
            if (array_key_exists($itemId, $validated['jumlah'])) {
                $qtyMap[$itemId] = (int) $validated['jumlah'][$itemId];
            } elseif (array_key_exists($idx, $validated['jumlah'])) {
                $qtyMap[$itemId] = (int) $validated['jumlah'][$idx];
            } else {
                $qtyMap[$itemId] = 1;
            }
        }
        $validated['qty_map'] = $qtyMap;

        $tglPinjam = Carbon::parse($validated['tgl_pinjam']);
        $tglKembali = Carbon::parse($validated['tgl_rencana_kembali']);

        foreach ($validated['item_ids'] as $idx => $itemId) {
            $item = Item::findOrFail($itemId);
            $minta = $qtyMap[$itemId] ?? 1;
            if ($minta > $item->jumlah_tersedia) {
                return back()->withErrors(["jumlah.{$idx}" => "Stok {$item->nama} tidak cukup (tersedia {$item->jumlah_tersedia})."])->withInput();
            }
        }

        $fotoPath = null;
        if ($request->hasFile('foto')) {
            $fotoPath = $request->file('foto')->store('handover');
        }

        $kode = $this->generateKode();

        $loan = DB::transaction(function () use ($validated, $kode, $fotoPath) {
            $loan = Loan::create([
                'kode_pinjam' => $kode,
                'user_id' => Auth::id(),
                'nama_peminjam' => $validated['nama_peminjam'],
                'email' => $validated['email'],
                'no_hp' => $validated['no_hp'],
                'department_id' => $validated['department_id'],
                'tgl_pinjam' => $validated['tgl_pinjam'],
                'tgl_rencana_kembali' => $validated['tgl_rencana_kembali'],
                'tujuan' => $validated['tujuan'],
                'status' => 'dipinjam',
                'is_walkin' => true,
                'approved_by' => Auth::id(),
                'handover_catatan' => $validated['catatan'] ?? null,
                'handover_foto' => $fotoPath,
                'handover_at' => now(),
            ]);

            foreach ($validated['item_ids'] as $idx => $itemId) {
                $qty = (int) ($validated['qty_map'][$itemId] ?? 1);
                $loan->loanItems()->create([
                    'item_id' => $itemId,
                    'jumlah' => $qty,
                    'kondisi_keluar' => 'Baik',
                ]);

                $item = Item::find($itemId);
                $item->jumlah_tersedia = max(0, $item->jumlah_tersedia - $qty);
                if ($item->jumlah_tersedia <= 0) {
                    $item->status = 'Dipinjam';
                }
                $item->save();
            }

            return $loan;
        });

        $this->kirimNotifikasi($loan, 'walkin', "Walk-in {$kode} langsung diserahterimakan (dipinjam).");
        $this->audit('loan_walkin', $loan, ['kode' => $kode]);

        return redirect()->route('admin.loans.show', $loan->id)->with('success', "Walk-in {$kode} berhasil dibuat.");
    }

    /* ============ EXTENSION & HILANG ============ */

    public function extensionApprove(Request $request, int $extensionId): RedirectResponse
    {
        $validated = $request->validate([
            'keputusan' => ['required', 'in:disetujui,ditolak'],
        ]);

        $ext = LoanExtension::with('loan')->findOrFail($extensionId);
        abort_if($ext->status !== 'diajukan', 400, 'Perpanjangan sudah diproses.');

        DB::transaction(function () use ($ext, $validated) {
            $ext->update([
                'status' => $validated['keputusan'],
                'approved_by' => Auth::id(),
            ]);

            if ($validated['keputusan'] === 'disetujui') {
                $ext->loan->update(['tgl_rencana_kembali' => $ext->tgl_baru]);
            }
        });

        $this->kirimNotifikasi($ext->loan, 'perpanjangan', "Perpanjangan {$ext->loan->kode_pinjam} {$validated['keputusan']}.");
        $this->audit('extension_'.$validated['keputusan'], $ext->loan, ['extension_id' => $ext->id]);

        return back()->with('success', 'Perpanjangan '.$validated['keputusan'].'.');
    }

    public function markLost(int $id): RedirectResponse
    {
        $loan = Loan::findOrFail($id);
        abort_if(! in_array($loan->status, ['dipinjam', 'terlambat', 'disetujui']), 400, 'Hanya loan aktif yang bisa ditandai hilang.');

        $loan->update(['status' => 'hilang']);
        $this->kirimNotifikasi($loan, 'hilang', "Peminjaman {$loan->kode_pinjam} ditandai HILANG.");
        $this->audit('loan_hilang', $loan, []);

        return redirect()->route('admin.loans.show', $loan->id)->with('success', 'Loan ditandai hilang.');
    }

    /* ============ HELPERS ============ */

    private function stokTerpakai($itemId, $tglPinjam, $tglKembali, $excludeLoanId = null): int
    {
        $q = Loan::whereHas('loanItems', fn ($w) => $w->where('item_id', $itemId))
            ->whereIn('status', ['disetujui', 'dipinjam', 'terlambat'])
            ->where('tgl_pinjam', '<=', Carbon::parse($tglKembali)->toDateString())
            ->where('tgl_rencana_kembali', '>=', Carbon::parse($tglPinjam)->toDateString());

        if ($excludeLoanId) {
            $q->where('id', '!=', $excludeLoanId);
        }

        return (int) $q->withSum(['loanItems as total' => fn ($w) => $w->where('item_id', $itemId)], 'jumlah')
            ->get()->sum('total');
    }

    private function generateKode(): string
    {
        $prefix = 'PJM-'.now()->format('Ymd').'-';
        do {
            $kode = $prefix.str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);
        } while (Loan::where('kode_pinjam', $kode)->exists());

        return $kode;
    }

    /** Tulis ke notifications + Log (tanpa SMTP sungguhan). */
    public function kirimNotifikasi(Loan $loan, string $tipe, string $pesan): void
    {
        NotificationLog::create([
            'tipe' => $tipe,
            'penerima' => $loan->email,
            'channel' => 'email',
            'loan_id' => $loan->id,
            'pesan' => $pesan,
            'status_kirim' => 'terkirim',
            'sent_at' => now(),
        ]);

        Log::info("[NOTIF][{$tipe}] {$loan->kode_pinjam} -> {$loan->email}: {$pesan}");
    }

    private function audit(string $aksi, Loan $loan, array $detail): void
    {
        AuditLog::create([
            'user_id' => Auth::id(),
            'aksi' => $aksi,
            'model_type' => Loan::class,
            'model_id' => $loan->id,
            'detail' => $detail,
            'ip' => request()->ip(),
        ]);
    }
}
