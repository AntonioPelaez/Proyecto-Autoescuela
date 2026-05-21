<?php

namespace App\Http\Controllers;

use App\Models\StudentSkillEvaluations;
use App\Models\StudentSkillsEvaluations;
use App\Models\DrivingSkill;
use App\Models\DrivingSkills;
use Illuminate\Http\Request;

class StudentSkillEvaluationsController extends Controller
{
    /**
     * Visualizar todas las evaluaciones de habilidades de los estudiantes
     */
    public function index()
    {
        $evaluations = StudentSkillEvaluations::with('studentProfile.user', 'teacherProfile.user', 'classSession', 'skillEvaluations.drivingSkill')->get();
        return response()->json($evaluations);
    }
    /**
     * Visualizar el histórico de evaluaciones de habilidades de un estudiante específico
     */
    public function history($studentProfileId)
    {
        $evaluations = StudentSkillEvaluations::with('teacherProfile.user', 'classSession', 'skillEvaluations.drivingSkill')
            ->where('student_profile_id', $studentProfileId)
            ->orderBy('created_at', 'desc')
            ->get();
        return response()->json($evaluations);
    }
    /**
     * Visualizar el progreso de habilidades de un estudiante a lo largo del tiempo
     */
    public function progress($studentProfileId)
    {
        $evaluations = StudentSkillEvaluations::with('teacherProfile.user', 'classSession', 'skillEvaluations.drivingSkill')
            ->where('student_profile_id', $studentProfileId)
            ->orderBy('created_at', 'desc')
            ->get();
        return response()->json($evaluations);
    }
     /**
     * Generar un resumen del progreso de habilidades de un estudiante, agrupando por habilidad y mostrando el promedio de puntuación y el número de evaluaciones para cada habilidad
     */
    public function summary($studentId)
    {
        $skills = DrivingSkills::all();
        $evaluations = StudentSkillEvaluations::where('student_profile_id', $studentId)
            ->with('skillEvaluations')
            ->get();

        $result = [];

        foreach ($skills as $skill) {
            $scores = [];

            foreach ($evaluations as $evaluation) {
                foreach ($evaluation->skillEvaluations as $s) {
                    if ($s->driving_skill_id === $skill->id) {
                        $scores[] = $s->score;
                    }
                }
            }

            $result[] = [
                'skill' => $skill->name,
                'average' => count($scores) ? round(array_sum($scores) / count($scores), 2) : null,
                'times_evaluated' => count($scores)
            ];
        }

        // Áreas débiles = habilidades con media < 6
        $weakAreas = array_filter($result, fn($s) => $s['average'] !== null && $s['average'] < 6);

        // Preparado para examen = si alguna evaluación lo marcó
        $ready = StudentSkillsEvaluations::whereHas('evaluation', function ($q) use ($studentId) {
            $q->where('student_profile_id', $studentId);
        })->where('ready_for_exam', true)->exists();

        return response()->json([
            'skills_summary' => $result,
            'weak_areas' => array_values($weakAreas),
            'ready_for_exam' => $ready
        ]);
    }
    /**
     * Visualizar un resumen del progreso de habilidades de un estudiante, destacando áreas fuertes y débiles
     */
     public function report($studentId)
    {
        $evaluations = StudentSkillEvaluations::where('student_profile_id', $studentId)
            ->with(['classSession', 'skillEvaluations.drivingSkill'])
            ->orderBy('created_at', 'desc')
            ->get();

        $summary = $this->summary($studentId)->getData();

        return response()->json([
            'student_id' => $studentId,
            'total_classes_evaluated' => $evaluations->count(),
            'summary' => $summary,
            'evaluations' => $evaluations
        ]);
    }
}
