<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Incidents;

class IncidentsController extends Controller
{
      public function index(Request $request)
    {
        $query = Incidents::with([
            'tipo',
            'reserva',
            'asignado',
            'profesor',
            'alumno'
        ]);

        // Filtros opcionales
        if ($request->estado) {
            $query->where('estado', $request->estado);
        }

        if ($request->prioridad) {
            $query->where('prioridad', $request->prioridad);
        }

        if ($request->tipo_id) {
            $query->where('tipo_id', $request->tipo_id);
        }

        if ($request->asignado_a) {
            $query->where('asignado_a', $request->asignado_a);
        }

        if ($request->responsable) {
            $query->where('responsable', $request->responsable);
        }

        if ($request->profesor_asignado) {
            $query->where('profesor_asignado', $request->profesor_asignado);
        }

        if ($request->alumno_asignado) {
            $query->where('alumno_asignado', $request->alumno_asignado);
        }

        if ($request->desde) {
            $query->whereDate('created_at', '>=', $request->desde);
        }

        if ($request->hasta) {
            $query->whereDate('created_at', '<=', $request->hasta);
        }

        return response()->json(
            $query->orderBy('created_at', 'desc')->get()
        );
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tipo_id'           => 'required|integer|exists:type_incidents,id',
            'prioridad'         => 'required|string',
            'descripcion'       => 'nullable|string',
            'reserva_id'        => 'nullable|integer|exists:class_sessions,id',
            'asignado_a'        => 'nullable|integer|exists:users,id',
            'responsable'       => 'nullable|string|in:alumno,profesor,externo',
            'profesor_asignado' => 'nullable|integer|exists:teacher_profiles,id',
            'alumno_asignado'   => 'nullable|integer|exists:student_profiles,id',
        ]);

        $incident = Incidents::create($validated);

        return response()->json([
            'message' => 'Incidencia creada correctamente',
            'data' => $incident->load(['tipo','reserva','asignado','profesor','alumno'])
        ], 201);
    }

    public function show(Incidents $incident)
    {
        return response()->json(
            $incident->load(['tipo','reserva','asignado','profesor','alumno'])
        );
    }

    public function update(Request $request, Incidents $incident)
    {
        $validated = $request->validate([
            'tipo_id'           => 'required|integer|exists:type_incidents,id',
            'prioridad'         => 'required|string',
            'estado'            => 'required|string',
            'descripcion'       => 'nullable|string',
            'reserva_id'        => 'nullable|integer|exists:class_sessions,id',
            'asignado_a'        => 'nullable|integer|exists:users,id',
            'responsable'       => 'nullable|string|in:alumno,profesor,externo',
            'profesor_asignado' => 'nullable|integer|exists:teacher_profiles,id',
            'alumno_asignado'   => 'nullable|integer|exists:student_profiles,id',
        ]);

        $incident->update($validated);

        return response()->json([
            'message' => 'Incidencia actualizada correctamente',
            'data' => $incident->load(['tipo','reserva','asignado','profesor','alumno'])
        ]);
    }

    public function destroy(Incidents $incident)
    {
        $incident->delete();

        return response()->json([
            'message' => 'Incidencia eliminada correctamente'
        ]);
    }
}
