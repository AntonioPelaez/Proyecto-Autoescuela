<?php

namespace App\Http\Controllers;

use App\Models\ExamResultStatus;
use Illuminate\Http\Request;

class ExamResultStatusController extends Controller
{
    public function index()
    {
        $statuses = ExamResultStatus::all();
        return response()->json($statuses);
    }
}
