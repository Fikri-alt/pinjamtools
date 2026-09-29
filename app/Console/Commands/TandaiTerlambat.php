<?php

namespace App\Console\Commands;

use App\Models\AuditLog;
use App\Models\Loan;
use App\Models\NotificationLog;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class TandaiTerlambat extends Command
{
    protected $signature = 'loans:tandai-terlambat {--dry-run : Tampilkan saja tanpa update}';

    protected $description = 'Ubah loans berstatus dipinjam/disetujui yang tgl_rencana_kembali < hari ini menjadi terlambat + notifikasi + audit';

    public function handle(): int
    {
        $today = Carbon::today()->toDateString();
        $dry = (bool) $this->option('dry-run');

        $loans = Loan::whereIn('status', ['dipinjam', 'disetujui'])
            ->where('tgl_rencana_kembali', '<', $today)
            ->get();

        $this->info("Ditemukan {$loans->count()} loan melewati rencana kembali (< {$today}).");

        if ($dry) {
            foreach ($loans as $l) {
                $this->line("- {$l->kode_pinjam} ({$l->status}, rencana {$l->tgl_rencana_kembali}) [DRY-RUN]");
            }

            return self::SUCCESS;
        }

        $count = 0;
        foreach ($loans as $loan) {
            $loan->update(['status' => 'terlambat']);

            NotificationLog::create([
                'tipe' => 'keterlambatan',
                'penerima' => $loan->email,
                'channel' => 'email',
                'loan_id' => $loan->id,
                'pesan' => "Peminjaman {$loan->kode_pinjam} telah melewati rencana kembali ({$loan->tgl_rencana_kembali}). Segera kembalikan barang.",
                'status_kirim' => 'terkirim',
                'sent_at' => now(),
            ]);

            AuditLog::create([
                'user_id' => null,
                'aksi' => 'otomatis_terlambat',
                'model_type' => Loan::class,
                'model_id' => $loan->id,
                'detail' => ['kode_pinjam' => $loan->kode_pinjam],
                'ip' => null,
            ]);

            Log::info("[TERLAMBAT] {$loan->kode_pinjam} ditandai terlambat.");
            $count++;
        }

        $this->info("Berhasil menandai {$count} loan sebagai terlambat.");

        return self::SUCCESS;
    }
}
