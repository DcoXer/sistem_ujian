<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class TeacherHomeroomStudentController extends Controller
{
    public function index(): View
    {
        return view('teacher.homeroom.students.index');
    }
}

