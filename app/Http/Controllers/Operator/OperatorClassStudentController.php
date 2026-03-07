<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use Illuminate\View\View;

class OperatorClassStudentController extends Controller
{
    public function index(SchoolClass $class): View
    {
        return view('operator.class-students', [
            'class' => $class,
        ]);
    }
}
