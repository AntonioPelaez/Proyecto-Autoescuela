<?php

namespace App\Http\Controllers;

use App\Models\ExamCallStatus;
use Illuminate\Http\Request;

class ExamCallStatusController extends Controller
{
    public function index()
    {
        $statuses = ExamCallStatus::all();
        return response()->json($statuses);
    }
}
