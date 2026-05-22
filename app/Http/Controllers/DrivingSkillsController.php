<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DrivingSkillsController extends Controller
{
    public function index()
    {
        $skills = \App\Models\DrivingSkills::all();
        return response()->json($skills);
    }
}
