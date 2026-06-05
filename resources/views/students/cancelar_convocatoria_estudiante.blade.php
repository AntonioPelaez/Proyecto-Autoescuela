@component('mail::message')
# Cancelación de convocatoria

Hola **{{ $student->name }} {{ $student->surnames }}**,  
tu convocatoria ha sido **cancelada**.

---

### 📌 Detalles de la convocatoria
- **Fecha:** {{ \Carbon\Carbon::parse($examCall->exam_date)->format('d/m/Y') }}
- **Hora:** {{ $examCall->start_time }} - {{ $examCall->end_time }}
- **Profesor:** {{ $examCall->teacher->name }} {{ $examCall->teacher->surnames }}

---

### ❗ Motivo de la cancelación
{{ $motive }}

---

Si tienes dudas, contacta con tu profesor o con administración.

Gracias,  
**Autoescuela**
@endcomponent
