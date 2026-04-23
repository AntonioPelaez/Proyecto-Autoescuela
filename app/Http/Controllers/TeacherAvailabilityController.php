<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Services\SlotGeneratorService;
use App\Models\Holiday;

class TeacherAvailabilityController extends Controller
{
    public function getAvailability($teacherId, Request $request, SlotGeneratorService $slotService)
    {
        $request->validate([
            'date' => 'required|date',
        ]);

        $date = $request->date;

        // Clave de caché generada por el Job
        $cacheKey = "availability:teacher:$teacherId:$date";

        // 1. Si existe en caché → devolver directamente
        if (Cache::has($cacheKey)) {
            return response()->json([
                'teacher_id' => $teacherId,
                'date' => $date,
                'slots' => Cache::get($cacheKey),
                'source' => 'cache'
            ]);
        }

        // 2. Si NO existe → generar al vuelo
        $slots = $slotService->generateSlots($teacherId, $date);

        // Guardar en caché por si se vuelve a pedir
        Cache::put($cacheKey, $slots, now()->addHours(6));

        return response()->json([
            'teacher_id' => $teacherId,
            'date' => $date,
            'slots' => $slots,
            'source' => 'live'
        ]);
    }
}
