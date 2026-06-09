<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Asistencia rechazada</title>
</head>
<body>
    <h2>Tu asistencia ha sido rechazada</h2>

    <p>Hola {{ $examStudent->student->user->name }} {{ $examStudent->student->user->surname1 }} {{ $examStudent->student->user->surname2 }},</p>

    <p>
        El profesor <strong>{{ $examStudent->examCall->teacher->user->name }} {{ $examStudent->examCall->teacher->user->surname1 }} {{ $examStudent->examCall->teacher->user->surname2 }}</strong>
        ha rechazado tu solicitud de asistencia a la siguiente convocatoria:
    </p>

    <ul>
        <li><strong>Fecha:</strong> {{ \Carbon\Carbon::parse($examStudent->examCall->exam_date)->format('d/m/Y') }}</li>
        <li><strong>Hora:</strong> {{ $examStudent->examCall->start_time }}</li>
    </ul>

    <p>Trabaja duro en las prácticas, que algún día estarás en el examen práctico.</p>

    <p>Un saludo,<br>Autoescuela AIBE</p>
</body>
</html>
