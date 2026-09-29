<?php

namespace App\Console\Commands;

use App\Models\Loan;
use App\Models\NotificationLog;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class KirimPengingat extends Command
{
    protected $signature = 'loans:kirim-pengingat';

    protected $description = 'Kirim pengingat H-1 & H-0 untuk loan aktif (tulis notifications + Log, mail via Log)';

    public function handle(): int
    {
        $today = Carbon::today();
        $tomorrow = Carbon::tomorrow()->toDateString();

        $h1 = Loan::whereIn('status', ['disetujui', 'dipinjam'])
            ->where('tgl_rencana_kembali', $tomorrow)->get();

        $h0 = Loan::whereIn('status', ['disetujui', 'dipinjam', 'terlambat'])
            ->where('tgl_rencana_kembali', $today->toDateString())->get();

        foreach ($h1 as $loan) {
            $pesan = "Pengingat H-1: {$loan->kode_pinjam} jatuh tempo besok ({$loan->tgl_rencana_kembali}).";
            $this->notif($loan, 'pengingat_h1', $pesan);
        }

        foreach ($h0 as $loan) {
            $pesan = "Pengingat H-0: {$loan->kode_pinjam} jatuh tempo HARI INI ({$loan->tgl_rencana_kembali}). Segera kembalikan.";
            $this->notif($loan, 'pengingat_h0', $pesan);
        }

        $this->info("Pengingat terkirim: H-1={$h1->count()}, H-0={$h0->count()}.");

        return self::SUCCESS;
    }

    private function notif(Loan $loan, string $tipe, string $pesan): void
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

        // "Kirim mail" via Log (tanpa SMTP sungguhan)
        Log::info("[MAIL-LOG][{$tipe}] To: {$loan->email} | {$pesan}");
    }
}
