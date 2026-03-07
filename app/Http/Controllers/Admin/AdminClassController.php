<?php

namespace App\Http\Controllers\Admin;

use App\Exports\StudentImportTemplateExport;
use App\Http\Controllers\Controller;
use App\Models\SchoolClass;
use App\Models\SchoolYear;
use App\Services\ClassStudentSyncService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;

class AdminClassController extends Controller
{
    public function __construct(
        protected ClassStudentSyncService $classStudentSyncService
    ) {
    }

    public function index(): View
    {
        return view('admin.classes.index');
    }

    public function storeSchoolYear(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:32'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);

        if (SchoolYear::query()->where('name', $data['name'])->exists()) {
            return back()->withErrors(['school_year' => 'Tahun ajaran sudah ada.'])->withInput();
        }

        SchoolYear::query()->create($data + ['is_active' => false]);

        return back()->with('status', 'school-year-created');
    }

    public function activateSchoolYear(SchoolYear $schoolYear): RedirectResponse
    {
        SchoolYear::query()->where('is_active', true)->update(['is_active' => false]);
        $schoolYear->update(['is_active' => true]);

        return back()->with('status', 'school-year-activated');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:64'],
            'grade_level' => ['nullable', 'integer', 'min:1', 'max:12'],
            'major' => ['nullable', 'string', 'max:64'],
            'school_year_id' => ['nullable', 'integer', Rule::exists('school_years', 'id')],
        ]);

        $exists = SchoolClass::query()
            ->where('school_year_id', $data['school_year_id'] ?? null)
            ->where('name', $data['name'])
            ->exists();

        if ($exists) {
            return back()->withErrors(['class' => 'Rombel dengan nama yang sama pada tahun ajaran ini sudah ada.'])->withInput();
        }

        SchoolClass::query()->create($data);

        return back()->with('status', 'class-created');
    }

    public function update(Request $request, SchoolClass $class): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:64'],
            'grade_level' => ['nullable', 'integer', 'min:1', 'max:12'],
            'major' => ['nullable', 'string', 'max:64'],
            'school_year_id' => ['nullable', 'integer', Rule::exists('school_years', 'id')],
        ]);

        $exists = SchoolClass::query()
            ->whereKeyNot($class->id)
            ->where('school_year_id', $data['school_year_id'] ?? null)
            ->where('name', $data['name'])
            ->exists();

        if ($exists) {
            return back()->withErrors(['class' => 'Rombel dengan nama yang sama pada tahun ajaran ini sudah ada.'])->withInput();
        }

        $class->update($data);

        return back()->with('status', 'class-updated');
    }

    public function syncStudents(Request $request, SchoolClass $class): RedirectResponse
    {
        $data = $request->validate([
            'students_raw' => ['required', 'string'],
        ]);

        $result = $this->classStudentSyncService->syncFromRawRows($class, $data['students_raw']);
        $created = (int) ($result['created'] ?? 0);
        $updated = (int) ($result['updated'] ?? 0);
        $skipped = (int) ($result['skipped'] ?? 0);
        $errors = (array) ($result['errors'] ?? []);

        $message = "Sync siswa selesai. Created: {$created}, Updated: {$updated}, Skipped: {$skipped}.";

        if (! empty($errors)) {
            return back()->with('status', $message)->withErrors(['student_sync' => implode(' ', array_slice($errors, 0, 3))]);
        }

        return back()->with('status', $message);
    }

    public function downloadStudentImportTemplate(Request $request)
    {
        $format = strtolower((string) $request->query('format', 'xlsx'));
        $export = new StudentImportTemplateExport();

        if ($format === 'csv') {
            return Excel::download($export, 'template-import-siswa.csv', \Maatwebsite\Excel\Excel::CSV);
        }

        return Excel::download($export, 'template-import-siswa.xlsx', \Maatwebsite\Excel\Excel::XLSX);
    }
}
