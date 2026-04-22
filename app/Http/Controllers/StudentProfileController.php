<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\StudentProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class StudentProfileController extends Controller
{
    public function index()
    {
        $students = StudentProfile::with('user')->get();
        return view('students.index', compact('students'));
    }

    public function create()
    {
        return view('students.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'      => 'required|string|max:80',
            'surname1'  => 'nullable|string|max:80',
            'surname2'  => 'nullable|string|max:80',
            'email'     => 'required|email|unique:users,email',
            'password'  => 'required|min:6',
            'phone'     => 'nullable|string|max:20',
            'dni'       => 'required|string|max:20',
            'birth_date'=> 'required|date',
        ]);

        $user = User::create([
            'name'      => $validated['name'],
            'surname1'  => $validated['surname1'] ?? null,
            'surname2'  => $validated['surname2'] ?? null,
            'email'     => $validated['email'],
            'password'  => Hash::make($validated['password']),
            'phone'     => $validated['phone'] ?? null,
            'role_id'   => 3, // alumno
        ]);

        StudentProfile::create([
            'user_id'       => $user->id,
            'dni'           => $validated['dni'],
            'birth_date'    => $validated['birth_date'],
        ]);

        return redirect()->route('students.index')->with('success', 'Alumno creado correctamente.');
    }

    public function edit(StudentProfile $student)
    {
        return view('students.edit', compact('student'));
    }

    public function update(Request $request, StudentProfile $student)
    {
        $validated = $request->validate([
            'name'      => 'required|string|max:80',
            'surname1'  => 'nullable|string|max:80',
            'surname2'  => 'nullable|string|max:80',
            'email'     => 'required|email|unique:users,email,' . $student->user_id,
            'phone'     => 'nullable|string|max:20',
            'dni'       => 'required|string|max:20',
            'birth_date'=> 'required|date',
        ]);

        $student->user->update([
            'name'      => $validated['name'],
            'surname1'  => $validated['surname1'] ?? null,
            'surname2'  => $validated['surname2'] ?? null,
            'email'     => $validated['email'],
            'phone'     => $validated['phone'] ?? null,
        ]);

        $student->update([
            'dni'        => $validated['dni'],
            'birth_date' => $validated['birth_date'],
        ]);

        return redirect()->route('students.index')->with('success', 'Alumno actualizado correctamente.');
    }

    public function destroy(StudentProfile $student)
    {
        $student->user->delete();
        $student->delete();

        return redirect()->route('students.index')->with('success', 'Alumno eliminado.');
    }

    public function notes(StudentProfile $student)
    {
        return view('students.notes', compact('student'));
    }

    public function saveNotes(Request $request, StudentProfile $student)
    {
        $student->update([
            'pickup_notes' => $request->pickup_notes,
        ]);

        return back()->with('success', 'Notas actualizadas.');
    }
}
