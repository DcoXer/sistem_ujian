<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HomeroomTeacher;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\TeacherSubject;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminAssignmentController extends Controller
{
    public function index(): View
    {
        return view('admin.assignments.index');
    }

    public function storeTeacherSubject(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'teacher_id' => [
                'required',
                'integer',
                Rule::exists('users', 'id')->where(fn ($q) => $q->where('role', User::ROLE_TEACHER)),
            ],
            'subject_id' => ['required', 'integer', Rule::exists('subjects', 'id')],
            'class_id' => ['required', 'integer', Rule::exists('school_classes', 'id')],
        ]);

        $exists = TeacherSubject::query()
            ->where('teacher_id', $data['teacher_id'])
            ->where('subject_id', $data['subject_id'])
            ->where('class_id', $data['class_id'])
            ->exists();

        if ($exists) {
            return back()->withErrors(['teacher_subject' => 'Assignment mapel-guru-kelas sudah ada.'])->withInput();
        }

        TeacherSubject::query()->create($data);

        return back()->with('status', 'teacher-subject-assigned');
    }

    public function destroyTeacherSubject(TeacherSubject $teacherSubject): RedirectResponse
    {
        $teacherSubject->delete();

        return back()->with('status', 'teacher-subject-removed');
    }

    public function storeHomeroom(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'teacher_id' => [
                'required',
                'integer',
                Rule::exists('users', 'id')->where(fn ($q) => $q->where('role', User::ROLE_TEACHER)),
            ],
            'class_id' => ['required', 'integer', Rule::exists('school_classes', 'id')],
        ]);

        HomeroomTeacher::query()->updateOrCreate(
            ['class_id' => $data['class_id']],
            ['teacher_id' => $data['teacher_id']]
        );

        return back()->with('status', 'homeroom-assigned');
    }

    public function destroyHomeroom(HomeroomTeacher $homeroomTeacher): RedirectResponse
    {
        $homeroomTeacher->delete();

        return back()->with('status', 'homeroom-removed');
    }
}
