@extends('layouts.app')

@section('content')

<div class="d-flex justify-content-center align-items-center" style="min-height: 70vh;">
    <div class="col-md-5">

        <div class="card shadow-sm">
            <div class="card-body">

                <h3 class="text-center mb-4">Registro de alumno</h3>

                <form method="POST" action="/api/auth/register" id="registerForm">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label">Nombre completo</label>
                        <input type="text" name="name" id="name" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" id="email" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Contraseña</label>
                        <input type="password" name="password" id="password" class="form-control" required>
                    </div>

                    <button type="submit" class="btn btn-success w-100">
                        Crear cuenta
                    </button>

                    <div class="text-center mt-3">
                        <a href="{{ route('login') }}">¿Ya tienes cuenta?</a>
                    </div>
                </form>

            </div>
        </div>

    </div>
</div>

<script>
document.getElementById('registerForm').addEventListener('submit', async function(e) {
    e.preventDefault();

    const response = await fetch('/api/auth/register', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            name: document.getElementById('name').value,
            email: document.getElementById('email').value,
            password: document.getElementById('password').value
        })
    });

    const data = await response.json();

    if (response.ok) {
        alert('Cuenta creada correctamente');
        window.location.href = '/auth/login';
    } else {
        alert(data.message || 'Error al registrar');
    }
});
</script>

@endsection
