<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Incidents;

class IncidentsController extends Controller
{
    public function index(Request $request)
    {
        $query = Incidents::with(['tipo', 'reserva', 'asignado', 'profesor', 'alumno']);

        // filtros...
        if ($request->estado) $query->where('estado', $request->estado);
        if ($request->prioridad) $query->where('prioridad', $request->prioridad);
        if ($request->tipo_id) $query->where('tipo_id', $request->tipo_id);
        if ($request->asignado_a) $query->where('asignado_a', $request->asignado_a);
        if ($request->responsable) $query->where('responsable', $request->responsable);
        if ($request->profesor_asignado) $query->where('profesor_asignado', $request->profesor_asignado);
        if ($request->alumno_asignado) $query->where('alumno_asignado', $request->alumno_asignado);
        if ($request->desde) $query->whereDate('created_at', '>=', $request->desde);
        if ($request->hasta) $query->whereDate('created_at', '<=', $request->hasta);

        $incidents = $query->orderBy('created_at', 'desc')->get();

        // 🔥 TRANSFORMACIÓN PARA EL FRONTEND
        $mapped = $incidents->map(function ($i) {
            return [
                'id'          => $i->id,
                'type'        => $i->tipo?->nombre ?? null,
                'priority'    => $i->prioridad,
                'status'      => $i->estado,
                'description' => $i->descripcion,
                'bookingId'   => $i->reserva_id,
                'assignedTo'  => $i->asignado_a,
                'responsable' => $i->responsable,
                'profesor'    => $i->profesor_asignado,
                'alumno'      => $i->alumno_asignado,
                'createdAt'   => $i->created_at?->toDateString(),
            ];
        });

        return response()->json($mapped);
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
            'data' => [
                'id'          => $incident->id,
                'type'        => $incident->tipo?->nombre,
                'priority'    => $incident->prioridad,
                'status'      => $incident->estado,
                'description' => $incident->descripcion,
                'bookingId'   => $incident->reserva_id,
                'assignedTo'  => $incident->asignado_a,
                'responsable' => $incident->responsable,
                'profesor'    => $incident->profesor_asignado,
                'alumno'      => $incident->alumno_asignado,
                'createdAt'   => $incident->created_at?->toDateString(),
            ]
        ], 201);
    }

    public function show(Incidents $incident)
    {
        $incident->load(['tipo', 'reserva', 'asignado', 'profesor', 'alumno']);

        return response()->json([
            'id'          => $incident->id,
            'type'        => $incident->tipo?->nombre,
            'priority'    => $incident->prioridad,
            'status'      => $incident->estado,
            'description' => $incident->descripcion,
            'bookingId'   => $incident->reserva_id,
            'assignedTo'  => $incident->asignado_a,
            'responsable' => $incident->responsable,
            'profesor'    => $incident->profesor_asignado,
            'alumno'      => $incident->alumno_asignado,
            'createdAt'   => $incident->created_at?->toDateString(),
        ]);
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
            'message' => 'Incidencia creada correctamente',
            'data' => [
                'id'          => $incident->id,
                'type'        => $incident->tipo?->nombre,
                'priority'    => $incident->prioridad,
                'status'      => $incident->estado,
                'description' => $incident->descripcion,
                'bookingId'   => $incident->reserva_id,
                'assignedTo'  => $incident->asignado_a,
                'responsable' => $incident->responsable,
                'profesor'    => $incident->profesor_asignado,
                'alumno'      => $incident->alumno_asignado,
                'createdAt'   => $incident->created_at?->toDateString(),
            ]
        ], 201);
    }

    public function destroy(Incidents $incident)
    {
        $incident->delete();

        return response()->json([
            'message' => 'Incidencia eliminada correctamente'
        ]);
    }
}
