<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Factura</title>

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            color: #333;
        }

        .header {
            text-align: center;
            margin-bottom: 25px;
        }

        .header h2 {
            margin: 0;
            font-size: 22px;
        }

        .info-table, .details-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .info-table td {
            padding: 4px 0;
        }

        .details-table th {
            background: #f0f0f0;
            padding: 6px;
            border: 1px solid #ccc;
            text-align: left;
        }

        .details-table td {
            padding: 6px;
            border: 1px solid #ccc;
        }

        .totals {
            width: 100%;
            margin-top: 20px;
        }

        .totals td {
            padding: 6px;
        }

        .totals .label {
            text-align: right;
            font-weight: bold;
        }

        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 11px;
            color: #777;
        }
    </style>
</head>

<body>

<div class="header">
    <h2>{{ $autoescuela['name'] }}</h2>
    <p>
        {{ $autoescuela['address'] }}<br>
        Tel: {{ $autoescuela['phone'] }} · {{ $autoescuela['email'] }}
    </p>
</div>

<table class="info-table">
    <tr>
        <td><strong>Factura Nº:</strong> {{ $payment->id }}</td>
        <td><strong>Fecha:</strong> {{ \Carbon\Carbon::parse($payment->paid_at)->format('d/m/Y') }}</td>
    </tr>
    <tr>
        <td><strong>Alumno:</strong> {{ $student->name }} {{ $student->surname1 }} {{ $student->surname2 }}</td>
        <td><strong>Email:</strong> {{ $student->email }}</td>
    </tr>
</table>

<h3>Detalles de la Reserva</h3>

<table class="details-table">
    <tr>
        <th>Concepto</th>
        <th>Fecha</th>
        <th>Horario</th>
        <th>Profesor</th>
        <th>Vehículo</th>
        <th>Importe</th>
    </tr>

    <tr>
        <td>Clase práctica (Ref: {{ $session->booking_reference }})</td>
        <td>{{ \Carbon\Carbon::parse($session->session_date)->format('d/m/Y') }}</td>
        <td>{{ $session->start_time }} - {{ $session->end_time }}</td>
        <td>{{ $teacher->name }} {{ $teacher->surname1 }}</td>
        <td>{{ $vehicle->brand }} {{ $vehicle->model }} ({{ $vehicle->plate_number }})</td>
        <td>{{ number_format($payment->amount, 2) }} €</td>
    </tr>
</table>

<table class="totals">
    <tr>
        <td class="label">Subtotal:</td>
        <td>{{ number_format($payment->amount, 2) }} €</td>
    </tr>
    <tr>
        <td class="label">Total:</td>
        <td><strong>{{ number_format($payment->amount, 2) }} €</strong></td>
    </tr>
</table>

<div class="footer">
    Gracias por confiar en nuestra autoescuela.
</div>

</body>
</html>
