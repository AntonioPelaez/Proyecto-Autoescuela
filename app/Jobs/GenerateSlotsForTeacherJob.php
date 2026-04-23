<?php

namespace App\Jobs;

use App\Services\SlotGeneratorService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;

class GenerateSlotsForTeacherJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $teacherId;
    public $month;

    public function __construct(int $teacherId, string $month)
    {
        $this->teacherId = $teacherId;
        $this->month = $month;
    }

    public function handle(SlotGeneratorService $slotService)
    {
        $start = Carbon::parse($this->month . '-01');
        $end = $start->copy()->endOfMonth();

        while ($start->lte($end)) {

            $date = $start->format('Y-m-d');

            $slots = $slotService->generateSlots($this->teacherId, $date);

            // Guardar en caché por 2 meses
            Cache::put(
                "availability:teacher:{$this->teacherId}:{$date}",
                $slots,
                now()->addMonths(2)
            );

            $start->addDay();
        }
    }
}
