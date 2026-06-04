@component('mail::message')
# Resumen de tu clase

Hola {{ $student->user->name }},

Aquí tienes el resumen completo de tu clase del día **{{ \Carbon\Carbon::parse($sessionDate)->format('d/m/Y') }}**.

@component('mail::panel')
**Ciudad:** {{ $session->town->name }}  
**Profesor:** {{ $teacher->user->name }}  
**Vehículo:** {{ $vehicle->brand }} {{ $vehicle->model }} ({{ $vehicle->plate_number }})  
**Hora de inicio:** {{ $startTime }}  
**Hora de fin:** {{ $endTime }}  
**Nota total:** {{ $score }}  
**¿Preparado para examen?:** {{ $readyForExam ? 'Sí' : 'No' }}
@endcomponent

---

## Habilidades trabajadas

@foreach($studentSkills as $skill)
- **{{ $skill->drivingSkill->name }}:** {{ $skill->score }}/10
@endforeach

---

@if($notes)
## Notas del profesor
@component('mail::panel')
{{ $notes }}
@endcomponent
@endif

Si deseas revisar tu progreso o reservar nuevas clases, puedes hacerlo desde tu panel de estudiante.

Gracias por confiar en Autoescuela AIBE.

@endcomponent
