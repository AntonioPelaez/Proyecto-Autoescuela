<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\TeacherProfile;
use App\Jobs\GenerateSlotsForTeacherJob;
use Carbon\Carbon;

class GenerateNextMonthSlots extends Command
{
    protected $signature = 'slots:generate-next-month';

    protected $description = 'Genera automáticamente los slots del mes siguiente para todos los profesores';

    public function handle()
    {
        $nextMonth = Carbon::now()->addMonth()->format('Y-m');

        $this->info("Generando slots para el mes: $nextMonth");

        $teachers = TeacherProfile::where('is_active_for_booking', true)->get();

        foreach ($teachers as $teacher) {
            GenerateSlotsForTeacherJob::dispatch($teacher->id, $nextMonth);
        }

        $this->info("Jobs lanzados correctamente.");
    }
}
