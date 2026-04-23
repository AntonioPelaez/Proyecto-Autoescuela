<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ClassSession;

class ClassSessionQueryController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'town_id' => 'required|integer',
            'date' => 'required|date',
        ]);

        $townId = $request->town_id;
        $date = $request->date;

        // Obtener reservas del pueblo y fecha
        $sessions = ClassSession::with(['teacherProfile.user', 'studentProfile.user', 'vehicle'])
            ->where('town_id', $townId)
            ->where('session_date', $date)
            ->orderBy('slot_starts_at')
            ->get();

        return response()->json([
            'town_id' => $townId,
            'date' => $date,
            'sessions' => $sessions
        ]);
    }
}
