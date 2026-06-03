@component('mail::message')
# Retirada de tu convocatoria
Hola, Lamentamos informarte que has sido retirado de tu convocatoria. A continuación puedes ver los detalles de la convocatoria de la que has sido retirado:
@component('mail::panel')
**Fecha del examen:** {{ $examCall->start_date }}
**Ciudad:** {{ $examCall->town->name }}
**Profesor:** {{ $examCall->teacher->user->name }}
**Vehículo:** {{ $examCall->vehicle->brand }} {{ $examCall->vehicle->model }} ({{ $examCall->vehicle->plate }})
**Estado actual:** {{ $examStudent->examResultStatus->name }}
**Motivo de la retirada:** {{ $motive }}
@endcomponent