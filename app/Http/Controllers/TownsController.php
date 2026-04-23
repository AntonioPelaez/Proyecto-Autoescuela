<?php

namespace App\Http\Controllers;

use App\Models\TeacherTown;
use Illuminate\Http\Request;
use App\Models\Town;

class TownsController extends Controller
{
    public function index()
{
    $towns = Town::select('towns.id', 'towns.name', 'towns.postal_code')
        ->join('teacher_towns', 'teacher_towns.town_id', '=', 'towns.id')
        ->join('teacher_profiles', 'teacher_profiles.id', '=', 'teacher_towns.teacher_profile_id')
        ->join('users', 'users.id', '=', 'teacher_profiles.user_id')
        ->groupBy('towns.id', 'towns.name', 'towns.postal_code')
        ->get();

    return response()->json($towns);
}
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

    public function store(Request $request){    
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
    public function update(Request $request, $id){
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
    public function destroy($id){ 
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
}
