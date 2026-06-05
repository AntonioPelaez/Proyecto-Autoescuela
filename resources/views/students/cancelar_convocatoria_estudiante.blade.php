<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Cancelación de convocatoria</title>
</head>
<body style="font-family: Arial, sans-serif; color: #333;">

    <h2>Cancelación de convocatoria</h2>

    <p>
        Hola <strong>{{ $student->user->name }} {{ $student->user->surname1 }} {{ $student->user->surname2 }}</strong>,
    </p>

    <p>Tu convocatoria ha sido <strong>cancelada</strong>.</p>

    <h3>Detalles de la convocatoria</h3>
    <ul>
        <li><strong>Fecha:</strong> {{ \Carbon\Carbon::parse($examCall->exam_date)->format('d/m/Y') }}</li>
        <li><strong>Hora:</strong> {{ $examCall->start_time }}</li>
        <li><strong>Profesor:</strong>
            {{ $examStudent->teacher->user->name }}
            {{ $examStudent->teacher->user->surname1 }}
            {{ $examStudent->teacher->user->surname2 }}
        </li>
    </ul>

    <h3>Motivo de la cancelación</h3>
    <p>{{ $motive }}</p>

    <p>Si tienes dudas, contacta con tu profesor o con administración.</p>

    <p><strong>Autoescuela</strong></p>

</body>
</html>
