<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TeacherProfile;
use App\Models\TeacherAvailabilityException;

class TeacherAvailabiltyExceptionsController extends Controller
{
    /**
     * Mostrar todas las excepciones de disponibilidad para el profesor autenticado.
     */
    public function index(Request $request)
    {
        $teacher = $request->user()->teacherProfile;

        if (!$teacher) {
            return response()->json(['message' => 'No eres profesor'], 403);
        }

        $exceptions = TeacherAvailabilityException::where('teacher_profile_id', $teacher->id)->get();

        return response()->json($exceptions);
    }
    /**
     * Mostrar una excepción de disponibilidad específica por su ID para el profesor autenticado.
     */
    public function show(Request $request, $id)
    {
        $teacher = $request->user()->teacherProfile;

        if (!$teacher) {
            return response()->json(['message' => 'No eres profesor'], 403);
        }

        $exception = TeacherAvailabilityException::where('id', $id)->where('teacher_profile_id', $teacher->id)->first();

        if (!$exception) {
            return response()->json(['message' => 'Excepción no encontrada'], 404);
        }

        return response()->json($exception);
    }
    /**
     * Crear una nueva excepción de disponibilidad para el profesor autenticado.
     */
    public function store(Request $request)
    {
        $request->validate([
            'exception_date' => 'required|date',
            'type' => 'required|string|max:20',
            'starts_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i',
            'town_id' => 'nullable|exists:towns,id',
            'reason' => 'nullable|string|max:150'
        ]);

        $teacher = $request->user()->teacherProfile;

        if (!$teacher) {
            return response()->json(['message' => 'No eres profesor'], 403);
        }

        $exception = TeacherAvailabilityException::create([
            'teacher_profile_id' => $teacher->id,
            'town_id' => $request->town_id,
            'exception_date' => $request->exception_date,
            'starts_time' => $request->starts_time,
            'end_time' => $request->end_time,
            'type' => $request->type,
            'reason' => $request->reason
        ]);

        return response()->json([
            'message' => 'Excepción registrada correctamente',
            'exception' => $exception
        ]);
    }
    /**
     * Actualizar una excepción de disponibilidad específica por su ID para el profesor autenticado.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'exception_date' => 'required|date',
            'type' => 'required|string|max:20',
            'starts_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i',
            'town_id' => 'nullable|exists:towns,id',
            'reason' => 'nullable|string|max:150'
        ]);

        $teacher = $request->user()->teacherProfile;

        if (!$teacher) {
            return response()->json(['message' => 'No eres profesor'], 403);
        }

        $exception = TeacherAvailabilityException::where('id', $id)->where('teacher_profile_id', $teacher->id)->first();

        if (!$exception) {
            return response()->json(['message' => 'Excepción no encontrada'], 404);
        }

        $exception->update([
            'town_id' => $request->town_id,
            'exception_date' => $request->exception_date,
            'starts_time' => $request->starts_time,
            'end_time' => $request->end_time,
            'type' => $request->type,
            'reason' => $request->reason
        ]);

        return response()->json([
            'message' => 'Excepción actualizada correctamente',
            'exception' => $exception
        ]);
    }
    /**
     * Eliminar una excepción de disponibilidad específica por su ID para el profesor autenticado.
     */
    public function destroy(Request $request, $id)
    {
        $teacher = $request->user()->teacherProfile;

        if (!$teacher) {
            return response()->json(['message' => 'No eres profesor'], 403);
        }

        $exception = TeacherAvailabilityException::where('id', $id)->where('teacher_profile_id', $teacher->id)->first();

        if (!$exception) {
            return response()->json(['message' => 'Excepción no encontrada'], 404);
        }

        $exception->delete();

        return response()->json(['message' => 'Excepción eliminada correctamente']);
    }
}
