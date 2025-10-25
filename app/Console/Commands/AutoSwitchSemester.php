<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Semester;
use Carbon\Carbon;
class AutoSwitchSemester extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'semester:auto-switch';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Aktifkan semester sesuai tanggal atau bulan (Ganjil: Jul–Des, Genap: Jan–Jun)';

    /**
     * Execute the console command.
     */
    public function handle()
    {$today = Carbon::now();

        if ($today->isSameDay(Carbon::create(2025, 10, 24))) {

            $month = $today->month;
            $semesterName = ($month >= 7 && $month <= 12) ? 'Ganjil' : 'Genap';

            Semester::query()->update(['active' => false]);
            Semester::where('name', $semesterName)->update(['active' => true]);

            $this->info("Cronjob dijalankan hari ini ({$today->toDateString()}) — Semester aktif: {$semesterName}");
        } else {
            $this->info("Cronjob tidak dijalankan. Tanggal sekarang: {$today->toDateString()}");
        }
    }
}
