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

    public function index(){
        $examCalls = ExamCalls::with(['examCallStatus', 'examStudents'])->orderBy('exam_date', 'desc')->orderBy('start_time', 'desc')->get();
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
    public function show($id){
        $examCall = ExamCalls::with(['examCallStatus',
            'examStudents.student.user',
            'examStudents.teacher.user',
            'examStudents.vehicle',
            'examStudents.examResultStatus'])->findOrFail($id);
        return response()->json($examCall);
    }

    /**
     * Crea una convocatoria de examen y asocia a los estudiantes seleccionados, verificando que estén preparados para el examen.
     */

    public function store(Request $request){
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
        ]);
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
        $examCall = ExamCalls::create($examcall);
        foreach ($examcall['students'] as $studentId) {
        ExamStudents::create([
            'exam_call_id' => $examCall->id,
            'student_id' => $studentId,
            'teacher_id' => $examcall['teacher_id'],
            'vehicle_id' => $examcall['vehicle_id'],
            'exam_result_status_id' => 1, // pendiente
            'result_notes' => null,
        ]);
    }
         return response()->json([
        'message' => 'Convocatoria creada correctamente',
        'exam_call' => $examCall->load(['examCallStatus', 'examStudents'])
    ], 201);

    }

    /**
     * Actualiza los detalles de una convocatoria de examen específica, incluyendo su estado, los estudiantes asociados, el profesor asignado y el vehículo utilizado.
     */
    public function update(Request $request, $id){
        $examCall = ExamCalls::findOrFail($id);
        $data = $request->validate([
            'town_id' => 'sometimes|exists:towns,id',
            'exam_date' => 'sometimes|date',
            'start_time' => 'sometimes',
            'exam_call_status_id' => 'sometimes|exists:exam_call_status,id',
            'teacher_id' => 'sometimes|exists:teacher_profiles,id',
            'vehicle_id' => 'sometimes|exists:vehicles,id',
            'notes' => 'nullable|string',
        ]);
        $examCall->update($data);
        return response()->json([
            'message' => 'Convocatoria actualizada correctamente',
            'exam_call' => $examCall->load(['examCallStatus', 'examStudents'])
        ]);
    }

    /**
     * Marca una convocatoria de examen como completada y actualiza su estado.
     */

    public function completeExamCall(Request $request, $id){
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

    public function cancelExamCall($id){
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
    $history = ExamStudents::with([
        'examCall.town',
        'examCall.examCallStatus',
        'teacher.user',
        'vehicle',
        'examResultStatus'
    ])
    ->where('student_id', $studentId)
    ->orderByDesc('id')
    ->get()
    ->map(function ($row) {
        return [
            'exam_call_id' => $row->exam_call_id,
            'exam_date' => $row->examCall->exam_date,
            'start_time' => $row->examCall->start_time,
            'town' => $row->examCall->town->name,
            'status_convocatoria' => $row->examCall->examCallStatus->name,
            'profesor' => $row->teacher->user->name . ' ' . $row->teacher->user->surname1,
            'vehicle' => $row->vehicle ? $row->vehicle->plate_number : null,
            'resultado' => $row->examResultStatus->name,
            'result_notes' => $row->result_notes,
        ];
    });

    return response()->json($history);
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
    ]);
}

}
