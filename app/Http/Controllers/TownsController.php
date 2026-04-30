<?php

namespace App\Http\Controllers;

use App\Models\TeacherTown;
use Illuminate\Http\Request;
use App\Models\Town;

class TownsController extends Controller
{
    /**
     * Mostrar todas las ciudades.
     */
    public function index()
    {
        $town = Town::select('id', 'name', 'postal_code', 'is_active')->get();
        return response()->json($town);
    }
    /**
     * Mostrar una ciudad específica por su ID, con sus nombre y código postal.
     */
    public function show($id)
    {
        $town = Town::select('towns.id', 'towns.name', 'towns.postal_code')
            ->join('teacher_towns', 'teacher_towns.town_id', '=', 'towns.id')
            ->join('teacher_profiles', 'teacher_profiles.id', '=', 'teacher_towns.teacher_profile_id')
            ->join('users', 'users.id', '=', 'teacher_profiles.user_id')
            ->where('towns.id', $id)
            ->first();

        if (!$town) {
            return response()->json(['message' => 'Ciudad no encontrada'], 404);
        }

        return response()->json($town);
    }
    /**
     * Crea una nueva ciudad con el nombre y código postal proporcionados. El nombre es obligatorio, mientras que el código postal es opcional.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:120',
            'postal_code' => 'nullable|string|max:12',
        ]);

        $town = new Town();
        $town->name = $request->name;
        $town->postal_code = $request->postal_code;
        $town->save();

        return response()->json(['message' => 'Ciudad creada correctamente', 'town' => $town], 201);
    }
    /**
     * Actualizar una ciudad específica por su ID, con el nombre y código postal proporcionados.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:120',
            'postal_code' => 'nullable|string|max:12',
        ]);

        $town = Town::find($id);
        if (!$town) {
            return response()->json(['message' => 'Ciudad no encontrada'], 404);
        }

        $town->name = $request->name;
        $town->postal_code = $request->postal_code;
        $town->save();

        return response()->json(['message' => 'Ciudad actualizada correctamente', 'town' => $town]);
    }
    /**
     * Eliminar una ciudad específica por su ID.
     */
    public function destroy($id)
    {
        $town = Town::find($id);
        if (!$town) {
            return response()->json(['message' => 'Ciudad no encontrada'], 404);
        }
        $teacherTowns = TeacherTown::where('town_id', $id);
        if ($teacherTowns->exists()) {
            return response()->json(['message' => 'No se puede eliminar la ciudad porque está asociada a un profesor'], 400);
        }
        $town->delete();
        return response()->json(['message' => 'Ciudad eliminada correctamente']);
    }
    public function toggleTown(Town $town)
    {
        $town->is_active = !$town->is_active;
        $town->save();

        return response()->json([
            'message' => 'Estado actualizado',
            'town' => $town
        ]);
    }
}
