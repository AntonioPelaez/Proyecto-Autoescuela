<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\TeacherAvailabilityException;

class TeacherAvailabiltyExceptionsController extends Controller
{
    /**
     * LISTAR EXCEPCIONES DEL PROFESOR AUTENTICADO
     */
    public function index(Request $request)
    {
        $teacher = $request->user()->teacherProfile;

        if (!$teacher) {
            return response()->json(['message' => 'No eres profesor'], 403);
        }

        $exceptions = TeacherAvailabilityException::where('teacher_profile_id', $teacher->id)->get();

        return response()->json([
            'teacher_id' => $teacher->id,
            'exceptions' => $exceptions
        ]);
    }

    /**
     * MOSTRAR UNA EXCEPCIÓN ESPECÍFICA
     */
    public function show(Request $request, $id)
    {
        $teacher = $request->user()->teacherProfile;

        if (!$teacher) {
            return response()->json(['message' => 'No eres profesor'], 403);
        }

        $exception = TeacherAvailabilityException::where('id', $id)
            ->where('teacher_profile_id', $teacher->id)
            ->first();

        if (!$exception) {
            return response()->json(['message' => 'Excepción no encontrada'], 404);
        }

        return response()->json([
            'teacher_id' => $teacher->id,
            'exception'  => $exception
        ]);
    }

    /**
     * CREAR UNA EXCEPCIÓN
     */
    public function store(Request $request)
    {
        $request->validate([
            'exception_date' => 'required|date',
            'type'           => 'required|string|max:20',
            'starts_time'    => 'nullable|date_format:H:i',
            'end_time'       => 'nullable|date_format:H:i',
            'town_id'        => 'nullable|exists:towns,id',
            'reason'         => 'nullable|string|max:150'
        ]);

        $teacher = $request->user()->teacherProfile;

        if (!$teacher) {
            return response()->json(['message' => 'No eres profesor'], 403);
        }

        $exception = TeacherAvailabilityException::create([
            'teacher_profile_id' => $teacher->id,
            'town_id'            => $request->town_id,
            'exception_date'     => $request->exception_date,
            'starts_time'        => $request->starts_time,
            'end_time'           => $request->end_time,
            'type'               => $request->type,
            'reason'             => $request->reason
        ]);

        return response()->json([
            'message'   => 'Excepción registrada correctamente',
            'exception' => $exception
        ]);
    }

    /**
     * ACTUALIZAR UNA EXCEPCIÓN
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'exception_date' => 'required|date',
            'type'           => 'required|string|max:20',
            'starts_time'    => 'nullable|date_format:H:i',
            'end_time'       => 'nullable|date_format:H:i',
            'town_id'        => 'nullable|exists:towns,id',
            'reason'         => 'nullable|string|max:150'
        ]);

        $teacher = $request->user()->teacherProfile;

        if (!$teacher) {
            return response()->json(['message' => 'No eres profesor'], 403);
        }

        $exception = TeacherAvailabilityException::where('id', $id)
            ->where('teacher_profile_id', $teacher->id)
            ->first();

        if (!$exception) {
            return response()->json(['message' => 'Excepción no encontrada'], 404);
        }

        $exception->update([
            'town_id'        => $request->town_id,
            'exception_date' => $request->exception_date,
            'starts_time'    => $request->starts_time,
            'end_time'       => $request->end_time,
            'type'           => $request->type,
            'reason'         => $request->reason
        ]);

        return response()->json([
            'message'   => 'Excepción actualizada correctamente',
            'exception' => $exception
        ]);
    }

    /**
     * ELIMINAR UNA EXCEPCIÓN
     */
    public function destroy(Request $request, $id)
    {
        $teacher = $request->user()->teacherProfile;

        if (!$teacher) {
            return response()->json(['message' => 'No eres profesor'], 403);
        }

        $exception = TeacherAvailabilityException::where('id', $id)
            ->where('teacher_profile_id', $teacher->id)
            ->first();

        if (!$exception) {
            return response()->json(['message' => 'Excepción no encontrada'], 404);
        }

        $exception->delete();

        return response()->json([
            'message' => 'Excepción eliminada correctamente'
        ]);
    }
}
