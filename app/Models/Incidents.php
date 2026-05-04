<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Incidents extends Model
{
    protected $fillable = [
        'tipo_id',
        'prioridad',
        'estado',
        'descripcion',
        'reserva_id',
        'asignado_a',
        'responsable',
        'profesor_asignado',
        'alumno_asignado',
    ];

    public function tipo()
    {
        return $this->belongsTo(TypeIncidents::class, 'tipo_id');
    }

    public function reserva()
    {
        return $this->belongsTo(ClassSession::class, 'reserva_id');
    }

    public function asignado()
    {
        return $this->belongsTo(User::class, 'asignado_a');
    }

    public function profesor()
    {
        return $this->belongsTo(TeacherProfile::class, 'profesor_asignado');
    }

    public function alumno()
    {
        return $this->belongsTo(StudentProfile::class, 'alumno_asignado');
    }
}
