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
        $examCalls = ExamCalls::with(['examCallStatus', 'examStudents', 'town'])->orderBy('exam_date', 'desc')->orderBy('start_time', 'desc')->get();
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

        return response()->json($examCall->examStudents);
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
            'student_confirmed' => true,
            'student_confirmed_at' => now(),
        ]);

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
}
