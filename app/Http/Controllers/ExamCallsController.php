<?php

namespace App\Http\Controllers;

use App\Models\ExamCalls;
use App\Models\ExamStudents;
use App\Models\StudentSkillEvaluations;
use Illuminate\Http\Request;

class ExamCallsController extends Controller
{
    /**
     * Devuelve una lista de todas las convocatorias de examen, incluyendo su estado y los estudiantes asociados a cada convocatoria.
     */

   public function index()
{
    $examCalls = ExamCalls::with([
        'examCallStatus',
        'town',
        'examStudents.student.user',
        'examStudents.teacher.user',
        'examStudents.vehicle'
    ])
    ->orderBy('exam_date', 'desc')
    ->orderBy('start_time', 'desc')
    ->get();

    return response()->json($examCalls);
}

    /**
     * Devuelve una lista de los estudiantes asociados a una convocatoria de examen específica, 
     * incluyendo su estado del resultado del examen, el profesor asignado y el vehículo utilizado.
     */

    public function listadoEstudiantes($id)
{
    $examCall = ExamCalls::with([
        'examStudents.student.user',
        'examStudents.teacher.user',
        'examStudents.vehicle',
        'examStudents.examResultStatus'
    ])->findOrFail($id);

    // 🔥 Asegurar que devolvemos teacher_approved y teacher_approved_at
    $students = $examCall->examStudents->map(function ($s) {
        return [
            'student_id' => $s->student_id,
            'student' => $s->student,
            'teacher' => $s->teacher,
            'vehicle' => $s->vehicle,
            'exam_result_status' => $s->examResultStatus,
            'result_notes' => $s->result_notes,
            'student_confirmed' => $s->student_confirmed,
            'student_confirmed_at' => $s->student_confirmed_at,
            'teacher_approved' => $s->teacher_approved,            // ✔ AÑADIDO
            'teacher_approved_at' => $s->teacher_approved_at,      // ✔ AÑADIDO
        ];
    });

    return response()->json($students);
}



    /**
     * Devuelve los detalles de una convocatoria de examen específica, incluyendo su estado, los estudiantes asociados, el profesor asignado y el vehículo utilizado.
     */
    public function show($id)
    {
        $examCall = ExamCalls::with([
            'town',
            'examCallStatus',
            'examStudents.student.user',
            'examStudents.teacher.user',
            'examStudents.vehicle',
            'examStudents.examResultStatus'
        ])->findOrFail($id);

        // Obtener profesor y vehículo desde el primer alumno
        $first = $examCall->examStudents->first();

        return response()->json([
            'id' => $examCall->id,
            'exam_date' => $examCall->exam_date,
            'start_time' => $examCall->start_time,
            'town_id' => $examCall->town_id,
            'town' => $examCall->town, // 🔥 AÑADIDO
            'teacher_id' => $first?->teacher_id,
            'vehicle_id' => $first?->vehicle_id,
            'notes' => $examCall->notes,
            'max_students' => $examCall->max_students,
            'exam_call_status' => $examCall->examCallStatus,
            'exam_students' => $examCall->examStudents,
        ]);
    }

    /**
     * Crea una convocatoria de examen y asocia a los estudiantes seleccionados, verificando que estén preparados para el examen.
     */

    public function store(Request $request)
{
    $examcall = $request->validate([
        'town_id' => 'required|exists:towns,id',
        'exam_date' => 'required|date',
        'start_time' => 'required',
        'exam_call_status_id' => 'required|exists:exam_call_status,id',
        'teacher_id' => 'required|exists:teacher_profiles,id',
        'vehicle_id' => 'required|exists:vehicles,id',
        'students' => 'required|array',
        'students.*' => 'exists:student_profiles,id',
        'notes' => 'nullable|string',
        'max_students' => 'nullable|integer|min:1',
    ]);

    // 1. Validar que todos los alumnos están aptos
    $studentsReady = StudentSkillEvaluations::whereIn('student_profile_id', $examcall['students'])
        ->where('ready_for_exam', 1)
        ->pluck('student_profile_id')
        ->unique()
        ->toArray();

    if (count($studentsReady) !== count($examcall['students'])) {
        return response()->json([
            'message' => 'Uno o más estudiantes NO están preparados para el examen.'
        ], 400);
    }

    // 2. Evitar duplicados SOLO con campos existentes
    $exists = ExamCalls::where('exam_date', $examcall['exam_date'])
        ->where('start_time', $examcall['start_time'])
        ->where('town_id', $examcall['town_id'])
        ->exists();

    if ($exists) {
        return response()->json([
            'message' => 'Ya existe una convocatoria con esa fecha, hora y localidad.'
        ], 400);
    }

    // 3. Crear convocatoria
    $examCall = ExamCalls::create([
        'town_id' => $examcall['town_id'],
        'exam_date' => $examcall['exam_date'],
        'start_time' => $examcall['start_time'],
        'exam_call_status_id' => $examcall['exam_call_status_id'],
        'notes' => $examcall['notes'] ?? null,
        'max_students' => $examcall['max_students'] ?? null,
    ]);

    // 4. Insertar alumnos en exam_students
    foreach ($examcall['students'] as $studentId) {
        ExamStudents::create([
            'exam_call_id' => $examCall->id,
            'student_id' => $studentId,
            'teacher_id' => $examcall['teacher_id'],
            'vehicle_id' => $examcall['vehicle_id'],
            'exam_result_status_id' => 1,
            'result_notes' => null,
            'student_confirmed' => false,
            'student_confirmed_at' => null,
        ]);
    }

    return response()->json([
        'message' => 'Convocatoria creada correctamente',
        'exam_call' => $examCall->load(['examCallStatus', 'examStudents'])
    ], 201);
}

    /**
     * Método privado que selecciona estudiantes automáticamente para una convocatoria de examen 
     * basada en la proporción de clases "apto" que han tenido en su evaluación de habilidades, 
     * limitando el número de estudiantes seleccionados al máximo permitido por la convocatoria.
     */
    private function selectStudentsByAptoRatio($maxStudents)
    {
        return StudentSkillEvaluations::with([
            'studentProfile',
            'studentProfile.classSessions'
        ])
            ->where('ready_for_exam', 1)
            ->get()
            ->map(function ($s) {

                $total = $s->studentProfile->classSessions->count();

                $aptas = $s->studentProfile->classSessions
                    ->where('status', 'apto')
                    ->count();

                // Ratio apto/total
                $ratio = $total > 0 ? $aptas / $total : 0;

                $s->ratio_apto = $ratio;

                return $s;
            })
            ->sortByDesc('ratio_apto')
            ->take($maxStudents)
            ->pluck('student_profile_id')
            ->values()
            ->toArray();
    }

    /**
     * Actualiza los detalles de una convocatoria de examen específica, incluyendo su estado, los estudiantes asociados, el profesor asignado y el vehículo utilizado.
     */
  public function update(Request $request, $id)
{
    $examCall = ExamCalls::findOrFail($id);

    // Validación
    $data = $request->validate([
        'town_id' => 'sometimes|exists:towns,id',
        'exam_date' => 'sometimes|date',
        'start_time' => 'sometimes',
        'exam_call_status_id' => 'sometimes|exists:exam_call_status,id',
        'teacher_id' => 'sometimes|exists:teacher_profiles,id',
        'vehicle_id' => 'sometimes|exists:vehicles,id',
        'notes' => 'nullable|string',
        'students' => 'array',
        'students.*' => 'exists:student_profiles,id',
        'max_students' => 'sometimes|integer|min:1',
    ]);

    // ---------------------------------------------------------
    // 🔥 1. Actualizar convocatoria
    // ---------------------------------------------------------
    $examCall->update($data);

    // ---------------------------------------------------------
    // 🔥 2. Actualizar profesor y vehículo en TODOS los exam_students
    // ---------------------------------------------------------
    ExamStudents::where('exam_call_id', $examCall->id)
        ->update([
            'teacher_id' => $data['teacher_id'] ?? $examCall->teacher_id,
            'vehicle_id' => $data['vehicle_id'] ?? $examCall->vehicle_id,
        ]);

    // ---------------------------------------------------------
    // 🔥 3. Sincronizar alumnos
    // ---------------------------------------------------------
    if (isset($data['students'])) {

        $existing = ExamStudents::where('exam_call_id', $examCall->id)
            ->pluck('student_id')
            ->toArray();

        $new = $data['students'];

        // Alumnos a eliminar
        $toDelete = array_diff($existing, $new);

        // Alumnos a añadir
        $toAdd = array_diff($new, $existing);

        // ---------------------------------------------------------
        // ❌ 3A. Eliminar alumnos que ya no están + sus datos asociados
        // ---------------------------------------------------------
        if (!empty($toDelete)) {

            foreach ($toDelete as $studentId) {
                
                // Finalmente borrar exam_students
                ExamStudents::where('exam_call_id', $examCall->id)
                    ->where('student_id', $studentId)
                    ->delete();
            }
        }

        // ---------------------------------------------------------
        // ✔ 3B. Añadir nuevos alumnos
        // ---------------------------------------------------------
        foreach ($toAdd as $studentId) {
            ExamStudents::create([
                'exam_call_id' => $examCall->id,
                'student_id' => $studentId,
                'teacher_id' => $data['teacher_id'] ?? $examCall->teacher_id,
                'vehicle_id' => $data['vehicle_id'] ?? $examCall->vehicle_id,
                'exam_result_status_id' => 1, // pendiente
                'student_confirmed' => false,
                'student_confirmed_at' => null,
            ]);
        }
    }

    // ---------------------------------------------------------
    // 🔥 4. Respuesta final
    // ---------------------------------------------------------
    return response()->json([
        'message' => 'Convocatoria actualizada correctamente',
        'exam_call' => $examCall->load([
            'examCallStatus',
            'examStudents.student',
            'examStudents.examResultStatus'
        ])
    ]);
}




    /**
     * Marca una convocatoria de examen como completada y actualiza su estado.
     */

    public function completeExamCall(Request $request, $id)
    {
        $request->validate([
            'result_notes' => 'nullable|string',
        ]);
        $examCall = ExamCalls::findOrFail($id);
        $examCall->update(['exam_call_status_id' => 2, 'result_notes' => $request->result_notes]);
        return response()->json([
            'message' => 'Convocatoria completada correctamente',
            'exam_call' => $examCall->load(['examCallStatus', 'examStudents'])
        ]);
    }

    /**
     * Marca una convocatoria de examen como cancelada y actualiza su estado.
     */

    public function cancelExamCall($id)
    {
        $examCall = ExamCalls::findOrFail($id);
        $examCall->update(['exam_call_status_id' => 3]);
        return response()->json([
            'message' => 'Convocatoria cancelada correctamente',
            'exam_call' => $examCall->load(['examCallStatus', 'examStudents'])
        ]);
    }

    /**
     * Actualiza el resultado de un estudiante específico en una convocatoria de examen, incluyendo su estado del resultado del examen y las notas del resultado.
     */
    public function updateExamStudentResult(Request $request, $examCallId, $studentId)
    {
        // Profesor autenticado
        $teacher = auth()->user()->teacherProfile;

        $request->validate([
            'exam_result_status_id' => 'required|exists:exam_result_statuses,id',
            'result_notes' => 'nullable|string',
        ]);

        // Buscar el registro del alumno en la convocatoria
        $examStudent = ExamStudents::where('exam_call_id', $examCallId)
            ->where('student_id', $studentId)
            ->firstOrFail();

        // 🔥 Validar que el profesor autenticado es el profesor asignado
        if ($examStudent->teacher_id !== $teacher->id) {
            return response()->json([
                'message' => 'No tienes permiso para modificar este resultado.'
            ], 403);
        }

        // Actualizar resultado
        $examStudent->update([
            'exam_result_status_id' => $request->exam_result_status_id,
            'result_notes' => $request->result_notes,
        ]);

        return response()->json([
            'message' => 'Resultado del estudiante actualizado correctamente',
            'exam_student' => $examStudent->load([
                'student.user',
                'teacher.user',
                'vehicle',
                'examResultStatus'
            ])
        ]);
    }


    /**
     * Devuelve una lista de estudiantes que están marcados como preparados para el examen, incluyendo su nombre y apellidos.
     */
    public function readyForExamList()
    {
        $students = StudentSkillEvaluations::where('ready_for_exam', true)
            ->with('studentProfile.user')
            ->get()
            ->pluck('studentProfile')
            ->unique('id')
            ->values()
            ->map(function ($student) {
                return [
                    'id' => $student->id,
                    'name' => $student->user->name,
                    'surname' => trim($student->user->surname1 . ' ' . $student->user->surname2),
                    'town' => $student->town ? $student->town->name : null,
                ];
            });

        return response()->json($students);
    }

    /**
     * Devuelve el historial de convocatorias de examen para un estudiante específico, 
     * incluyendo la fecha del examen, el estado de la convocatoria, el profesor asignado, el vehículo utilizado y el resultado del examen.
     */

    public function examHistoryByStudent($studentId)
    {
        $records = ExamStudents::with([
            'examCall.town',
            'examResultStatus',
        ])
            ->where('student_id', $studentId)
            ->whereHas('examResultStatus', function ($q) {
                $q->where('name', '!=', 'pendiente');
            })
            ->get();

        return $records->map(function ($r) {
            return [
                'date' => $r->examCall->exam_date,
                'result' => $r->examResultStatus->label ?? $r->examResultStatus->name,
                'notes' => $r->result_notes,
                'status' => 'finalizada',
            ];
        });
    }
    /**
     * Devuelve estadísticas de resultados de examen para un profesor específico, incluyendo el número total de estudiantes examinados, el número de aprobados, el número de suspendidos y los porcentajes correspondientes.
     */
    public function examStats($teacherId)
{
    $total = ExamStudents::where('teacher_id', $teacherId)
        ->whereIn('exam_result_status_id', [2, 3])
        ->count();

    $aprobados = ExamStudents::where('teacher_id', $teacherId)
        ->where('exam_result_status_id', 2)
        ->count();

    $suspendidos = ExamStudents::where('teacher_id', $teacherId)
        ->where('exam_result_status_id', 3)
        ->count();

    $porcentajeAprobados = $total > 0 ? round(($aprobados / $total) * 100, 2) : 0;
    $porcentajeSuspendidos = $total > 0 ? round(($suspendidos / $total) * 100, 2) : 0;

    return response()->json([
        'teacher_id' => $teacherId,
        'total_examinados' => $total,
        'aprobados' => $aprobados,
        'suspendidos' => $suspendidos,
        'porcentaje_aprobados' => $porcentajeAprobados,
        'porcentaje_suspendidos' => $porcentajeSuspendidos,

        // 🔥 Datos listos para la gráfica circular
        'chart' => [
            'labels' => ['Aprobados', 'Suspendidos'],
            'data' => [$aprobados, $suspendidos],
            'backgroundColor' => ['#28a745', '#dc3545'], // verde / rojo
            'borderColor' => '#ffffff',
        ]
    ]);
}

    /**
     * Marca la siguiente convocatoria de examen pendiente como programada, actualizando su estado. 
     * Si no hay convocatorias pendientes, devuelve un mensaje indicando que no hay convocatorias para completar.
     */
    public function toggle($id)
    {
        $examCall = ExamCalls::findOrFail($id);
        if ($examCall->exam_call_status_id == 3) {
            $examCall->update(['exam_call_status_id' => 1]);
            return response()->json([
                'message' => 'Convocatoria marcada como programada',
                'exam_call' => $examCall->load(['examCallStatus', 'examStudents'])
            ]);
        } elseif ($examCall->exam_call_status_id == 1) {
            $examCall->update(['exam_call_status_id' => 3]);
            return response()->json([
                'message' => 'Convocatoria marcada como cancelada',
                'exam_call' => $examCall->load(['examCallStatus', 'examStudents'])
            ]);
        } else {
            return response()->json([
                'message' => 'La convocatoria no se puede marcar como programada o cancelada porque ya está completada.'
            ], 400);
        }
    }
    /**
     * Devuelve la siguiente convocatoria de examen pendiente, ordenada por fecha y hora de inicio. 
     * Si no hay convocatorias pendientes, devuelve un mensaje indicando que no hay convocatorias para completar.
     */
    public function nextConvocation()
    {
        $next = ExamCalls::where('exam_call_status_id', 1)
            ->orderBy('exam_date')
            ->orderBy('start_time')
            ->first();

        if ($next) {

            // 🔥 Cargar TODAS las relaciones necesarias para el frontend
            $next->load([
                'examCallStatus',
                'examStudents.student.user',
                'examStudents.teacher.user',
                'examStudents.vehicle'
            ]);

            return response()->json([
                'message' => 'Siguiente convocatoria pendiente encontrada',
                'exam_call' => $next
            ]);
        }

        return response()->json([
            'message' => 'No hay convocatorias pendientes para completar.'
        ], 404);
    }
    /**
     * Función que permite a un estudiante confirmar su asistencia a una convocatoria de examen específica, 
     * actualizando el estado de confirmación del estudiante y la fecha y hora de confirmación.
     */
    public function confirmAttendance(Request $request, $examCallId, $studentId)
{
    if (auth()->user()->studentProfile->id != $studentId) {
        return response()->json([
            'message' => 'No tienes permiso para confirmar la asistencia de este estudiante.'
        ], 403);
    }

    // Buscar registro existente
    $examStudent = ExamStudents::where('exam_call_id', $examCallId)
        ->where('student_id', $studentId)
        ->first();

    // 🔥 Si NO existe → crearlo
    if (!$examStudent) {
        $examCall = ExamCalls::findOrFail($examCallId);

        $examStudent = ExamStudents::create([
            'exam_call_id' => $examCallId,
            'student_id' => $studentId,
            'teacher_id' => $examCall->examStudents->first()->teacher_id ?? null,
            'vehicle_id' => $examCall->examStudents->first()->vehicle_id ?? null,
            'exam_result_status_id' => 1,
            'student_confirmed' => true,
            'student_confirmed_at' => now(),
        ]);
    } else {
        // Si existe → actualizar
        $examStudent->update([
            'student_confirmed' => true,
            'student_confirmed_at' => now(),
        ]);
    }

    return response()->json([
        'message' => 'Asistencia confirmada correctamente',
        'exam_student' => $examStudent->load(['examCall', 'examResultStatus'])
    ]);
}

    /**
     * Función que permite a un estudiante desconfirmar su asistencia a una convocatoria de examen específica, 
     * actualizando el estado de confirmación del estudiante y la fecha y hora de confirmación a null.
     */
    public function unconfirmAttendance(Request $request, $examCallId, $studentId)
    {
        $examStudent = ExamStudents::where('exam_call_id', $examCallId)
            ->where('student_id', $studentId)
            ->firstOrFail();

        if (auth()->user()->studentProfile->id != $studentId) {
            return response()->json([
                'message' => 'No tienes permiso para confirmar la asistencia de este estudiante.'
            ], 403);
        }


        // Actualizar estado de confirmación
        $examStudent->update([
            'student_confirmed' => false,
            'student_confirmed_at' => null,
            'teacher_approved' => false,
    'teacher_approved_at' => null,
    'exam_result_status_id' => 4, // No presentado
        ]);

        return response()->json([
            'message' => 'Asistencia desconfirmada correctamente',
            'exam_student' => $examStudent->load(['examCall', 'examResultStatus'])
        ]);
    }
    /**
     * Historial de convocatorias para el alumno, sin tener duiplicados.
     */
    public function studentConvocationHistory($studentId)
{
    $records = ExamStudents::with([
        'examCall.town',
        'examCall.examCallStatus',
        'examResultStatus'
    ])
    ->where('student_id', $studentId)
    ->get()
    ->groupBy('exam_call_id') // ❗ evita duplicados
    ->map(function ($group) {
        $r = $group->first();

        return [
            'exam_call_id' => $r->exam_call_id,
            'date' => $r->examCall->exam_date,
            'time' => $r->examCall->start_time,
            'student_confirmed' => $r->student_confirmed,
            'exam_status' => $r->examResultStatus->label ?? $r->examResultStatus->name,
            'convocatoria_status' => $r->examCall->examCallStatus->name,
        ];
    })
    ->values();

    return response()->json($records);
}
/**
 * Borrar convocatoria de examen, eliminando también los registros asociados en exam_students.
 * Solo se permite borrar convocatorias que no estén completadas.
 */
public function destroy($id)
{
    $examCall = ExamCalls::findOrFail($id);
    if ($examCall->exam_call_status_id == 2) {
        return response()->json([
            'message' => 'No se puede eliminar una convocatoria que ya está completada.'
        ], 400);
    }
    // Eliminar registros asociados en exam_students
    ExamStudents::where('exam_call_id', $examCall->id)->delete();
    // Eliminar convocatoria
    $examCall->delete();
    return response()->json([
        'message' => 'Convocatoria eliminada correctamente'
    ]);
}
/**
 * Función que permite a un profesor aprobar a un estudiante específico en una convocatoria de examen,
 * actualizando el estado de aprobación del estudiante y la fecha y hora de aprobación.
 */
public function approveStudent(Request $request, $examCallId, $studentId)
{
     $examStudent = ExamStudents::where('exam_call_id', $examCallId)
        ->where('student_id', $studentId)
        ->firstOrFail();

    if (auth()->user()->teacherProfile->id != $examStudent->teacher_id) {
        return response()->json([
            'message' => 'Tiene que ser el profesor asignado a este estudiante en esta convocatoria.'
        ], 403);
    }

    $examStudent->update([
        'teacher_approved' => true,
        'teacher_approved_at' => now(),
    ]);

    return response()->json([
        'message' => 'Estudiante aprobado correctamente',
        'exam_student' => $examStudent->load(['examCall', 'examResultStatus'])
    ]);
}
/**
 * Función que permite a un profesor desaprobar a un estudiante específico en una convocatoria de examen,
 * actualizando el estado de aprobación del estudiante y la fecha y hora de desaprobación.
 */
public function unapproveStudent(Request $request, $examCallId, $studentId)
{
    $examStudent = ExamStudents::where('exam_call_id', $examCallId)
        ->where('student_id', $studentId)
        ->firstOrFail();

    if (auth()->user()->teacherProfile->id != $examStudent->teacher_id) {
        return response()->json([
            'message' => 'No tienes permiso para desaprobar a este estudiante.'
        ], 403);
    }

    $examStudent->update([
        'teacher_approved' => false,
        'teacher_approved_at' => null,
    ]);

    return response()->json([
        'message' => 'Estudiante desaprobado correctamente',
        'exam_student' => $examStudent->load(['examCall', 'examResultStatus'])
    ]);
}
/**
 * Función privada que calcula el número de plazas restantes para una convocatoria de examen específica,
 * restando el número de estudiantes actualmente inscritos del número máximo de estudiantes permitido por la convocatoria.
 * Si la convocatoria no tiene un límite de estudiantes, devuelve null.
 */
private function getRemainingSeats($examCallId)
{
    $examCall = ExamCalls::findOrFail($examCallId);

    if (!$examCall->max_students || $examCall->max_students <= 0) {
        return null; // ilimitado
    }

    $current = ExamStudents::where('exam_call_id', $examCallId)->count();

    return max($examCall->max_students - $current, 0);
}

/**
 * Generar un listado de estudiantes aceptados por el profesor
 * y además poder quitar alumnos de esa convocatoria si
 * el alumno le ha pasado algo y no puede presentarse al examen, o el profesor ha decidido que no lo aprueba.
 * 
 */
public function listedApprovedStudents($examCallId)
{
    $approvedStudents = ExamStudents::with([
        'student.user',
        'teacher.user',
        'vehicle',
        'examResultStatus'
    ])
    ->where('exam_call_id', $examCallId)
    ->where('teacher_approved', true)
    ->get();
    return response()->json($approvedStudents);
}
/**
 * Función que permite a un profesor quitar a un estudiante específico de la lista de aprobados en una convocatoria de examen,
 * actualizando el estado de aprobación del estudiante y la fecha y hora de desaprobación.
 */
public function removeApprovedStudent(Request $request, $examCallId, $studentId)
{
    $examStudent = ExamStudents::where('exam_call_id', $examCallId)
        ->where('student_id', $studentId)
        ->firstOrFail();

    if (auth()->user()->teacherProfile->id != $examStudent->teacher_id) {
        return response()->json([
            'message' => 'Tiene que ser el profesor asignado a este estudiante en esta convocatoria.'
        ], 403);
    }

    // 🔥 NO BORRAR — solo marcar como no presentado
    $examStudent->update([
        'exam_result_status_id' => 4, // No presentado
        'result_notes' => $request->result_notes ?? null,
        'student_confirmed' => false,
        'student_confirmed_at' => null,
        'teacher_approved' => false,
        'teacher_approved_at' => null,
    ]);

    return response()->json([
        'message' => 'Estado actualizado correctamente',
        'remaining_seats' => $this->getRemainingSeats($examCallId)
    ]);
}

/**
 * Generar un listado de estudiantes pendientes de aprobación por el profesor
 * y además poder añadir alumnos a esa convocatoria si el alumno le ha pasado algo y no puede presentarse al examen, o el profesor ha decidido que no lo aprueba.
 */
public function listPendingApprovalStudents($examCallId)
{
    $examCall = ExamCalls::findOrFail($examCallId);

    $remaining = $this->getRemainingSeats($examCallId);

    // 1️⃣ Alumnos dentro (NO aprobados)
    $pendingInside = ExamStudents::with([
        'student.user',
        'teacher.user',
        'vehicle',
        'examResultStatus'
    ])
    ->where('exam_call_id', $examCallId)
->where('teacher_approved', false)
->where('exam_result_status_id', 1) // 1 = pendiente

    
    ->get();

    // IDs dentro
    $insideIds = $pendingInside->pluck('student_id')->toArray();

    // 2️⃣ Si no hay plazas → NO mostrar alumnos fuera
    if ($remaining !== null && $remaining <= 0) {
        return response()->json([
            'pending_inside' => $pendingInside,
            'pending_outside' => [],
            'remaining_seats' => 0
        ]);
    }

    // 3️⃣ Alumnos aptos fuera
    $studentsReady = StudentSkillEvaluations::where('ready_for_exam', true)
        ->with('studentProfile.user')
        ->get()
        ->pluck('studentProfile')
        ->unique('id')
        ->values();

    // ❗ EXCLUSIÓN CRÍTICA:
    // - No incluir alumnos que YA están aprobados
    // - No incluir alumnos que YA están dentro
    $approvedIds = ExamStudents::where('exam_call_id', $examCallId)
        ->where('teacher_approved', true)
        ->pluck('student_id')
        ->toArray();

    $pendingOutside = $studentsReady
        ->filter(fn($s) =>
            !in_array($s->id, $insideIds) &&
            !in_array($s->id, $approvedIds)
        )
        ->map(function ($s) {
            return [
                'id' => $s->id,
                'name' => $s->user->name,
                'surname' => trim($s->user->surname1 . ' ' . $s->user->surname2),
                'town' => $s->town ? $s->town->name : null,
            ];
        })
        ->values();

    return response()->json([
        'pending_inside' => $pendingInside,
        'pending_outside' => $pendingOutside,
        'remaining_seats' => $remaining
    ]);
}



/**
 * Función que permite a un profesor añadir a un estudiante específico a la lista de aprobados en una convocatoria de examen,
 * actualizando el estado de aprobación del estudiante y la fecha y hora de aprobación.
 */
public function addApprovedStudent(Request $request, $examCallId, $studentId)
{
    // Buscar si ya existe
    $examStudent = ExamStudents::where('exam_call_id', $examCallId)
        ->where('student_id', $studentId)
        ->first();

    if ($examStudent) {
        // Ya existe → solo actualizar
        $examStudent->update([
            'teacher_approved' => true,
            'teacher_approved_at' => now(),
            'exam_result_status_id' => 1, // pendiente
            'student_confirmed' => false,
            'student_confirmed_at' => null,
        ]);

        return response()->json([
            'message' => 'Alumno aprobado correctamente (actualizado)',
            'exam_student' => $examStudent
        ]);
    }

    // Si NO existe → crearlo
    $examCall = ExamCalls::findOrFail($examCallId);

    $examStudent = ExamStudents::create([
        'exam_call_id' => $examCallId,
        'student_id' => $studentId,
        'teacher_id' => $examCall->examStudents->first()->teacher_id ?? null,
        'vehicle_id' => $examCall->examStudents->first()->vehicle_id ?? null,
        'exam_result_status_id' => 1,
        'teacher_approved' => true,
        'teacher_approved_at' => now(),
        'student_confirmed' => false,
        'student_confirmed_at' => null,
    ]);

    return response()->json([
        'message' => 'Alumno añadido y aprobado correctamente',
        'exam_student' => $examStudent
    ]);
}

}
