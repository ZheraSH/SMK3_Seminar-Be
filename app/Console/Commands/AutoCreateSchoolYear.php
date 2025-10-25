<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SchoolYear;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class AutoCreateSchoolYear extends Command
{
    protected $signature = 'auto:create-schoolyear';
    protected $description = 'Membuat tahun ajaran baru otomatis setiap menit (untuk testing)';

    public function handle()
    {
        $now = Carbon::now();
        $currentYear = $now->year;
        $nextYear = $currentYear + 1;
        $schoolYearName = "{$currentYear}/{$nextYear}";

        SchoolYear::where('active', true)->update(['active' => false]);

        $exists = SchoolYear::where('name', $schoolYearName)->exists();

        if (!$exists) {
            SchoolYear::create([
                'name' => $schoolYearName,
                'active' => true,
            ]);

            Log::info("Tahun ajaran baru dibuat otomatis: {$schoolYearName}");
            $this->info("✅ Tahun ajaran {$schoolYearName} berhasil dibuat otomatis.");
        } else {
            $this->info("ℹ️ Tahun ajaran {$schoolYearName} sudah ada, tidak dibuat ulang.");
        }

        file_put_contents(storage_path('logs/last_cronjob.txt'), now());
    }
}
