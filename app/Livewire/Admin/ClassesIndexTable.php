<?php

namespace App\Livewire\Admin;

use App\Imports\StudentSyncRowsImport;
use App\Livewire\Concerns\WithCrudNotifications;
use App\Models\SchoolClass;
use App\Models\SchoolYear;
use App\Models\Semester;
use App\Models\User;
use App\Services\ClassStudentSyncService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;

class ClassesIndexTable extends Component
{
    use WithCrudNotifications;
    use WithFileUploads;
    use WithPagination;

    public string $schoolYearName = '';

    public string $schoolYearStart = '';

    public string $schoolYearEnd = '';

    public bool $showSchoolYearModal = false;

    public string $className = '';

    public string $classGrade = '';

    public string $classMajor = '';

    public string $classSchoolYearId = '';

    public bool $showCreateClassModal = false;

    public bool $showEditClassModal = false;

    public ?int $editingClassId = null;

    public string $editClassName = '';

    public string $editClassGrade = '';

    public string $editClassMajor = '';

    public string $editClassSchoolYearId = '';

    public bool $showSyncModal = false;

    public ?int $syncClassId = null;

    public string $studentsRaw = '';

    public ?TemporaryUploadedFile $studentsFile = null;

    public function openSchoolYearModal(): void
    {
        $this->resetValidation();
        $this->showSchoolYearModal = true;
    }

    public function closeSchoolYearModal(): void
    {
        $this->showSchoolYearModal = false;
        $this->reset(['schoolYearName', 'schoolYearStart', 'schoolYearEnd']);
    }

    public function openCreateClassModal(): void
    {
        $this->resetValidation();
        $this->showCreateClassModal = true;
    }

    public function closeCreateClassModal(): void
    {
        $this->showCreateClassModal = false;
        $this->reset(['className', 'classGrade', 'classMajor', 'classSchoolYearId']);
    }

    public function createSchoolYear(): void
    {
        $data = $this->validateWithFriendlyMessage([
            'schoolYearName' => ['required', 'string', 'max:32'],
            'schoolYearStart' => ['nullable', 'date'],
            'schoolYearEnd' => ['nullable', 'date', 'after_or_equal:schoolYearStart'],
        ]);

        if (SchoolYear::query()->where('name', $data['schoolYearName'])->exists()) {
            $this->addError('schoolYearName', 'Tahun ajaran sudah ada.');
            $this->notifyError('Tahun ajaran ini sudah ada. Coba nama tahun ajaran yang lain.');

            return;
        }

        SchoolYear::query()->create([
            'name' => $data['schoolYearName'],
            'start_date' => $data['schoolYearStart'] ?: null,
            'end_date' => $data['schoolYearEnd'] ?: null,
            'is_active' => false,
        ]);

        $this->closeSchoolYearModal();
        session()->flash('status', 'school-year-created');
        $this->notifySuccess('Tahun ajaran baru berhasil ditambahkan.');
    }

    public function activateSchoolYear(int $schoolYearId): void
    {
        SchoolYear::query()->where('is_active', true)->update(['is_active' => false]);
        SchoolYear::query()->whereKey($schoolYearId)->update(['is_active' => true]);
        session()->flash('status', 'school-year-activated');
        $this->notifySuccess('Tahun ajaran aktif berhasil diubah.');
    }

    public function createSemester(int $schoolYearId, int $semesterNumber, ?string $startDate, ?string $endDate): void
    {
        $exists = Semester::query()
            ->where('school_year_id', $schoolYearId)
            ->where('semester_number', $semesterNumber)
            ->exists();

        if ($exists) {
            $this->notifyError('Semester '.$semesterNumber.' sudah ada di tahun ajaran ini.');

            return;
        }

        Semester::query()->create([
            'school_year_id' => $schoolYearId,
            'semester_number' => $semesterNumber,
            'name' => 'Semester '.$semesterNumber,
            'start_date' => $startDate ?: null,
            'end_date' => $endDate ?: null,
            'is_active' => false,
        ]);

        session()->flash('status', 'semester-created');
        $this->notifySuccess('Semester berhasil ditambahkan.');
    }

    public function activateSemester(int $semesterId): void
    {
        Semester::query()->where('is_active', true)->update(['is_active' => false]);
        Semester::query()->whereKey($semesterId)->update(['is_active' => true]);
        session()->flash('status', 'semester-activated');
        $this->notifySuccess('Semester aktif berhasil diubah.');
    }

    public function createClass(): void
    {
        $data = $this->validateWithFriendlyMessage([
            'className' => ['required', 'string', 'max:64'],
            'classGrade' => ['nullable', 'integer', 'min:1', 'max:12'],
            'classMajor' => ['nullable', 'string', 'max:64'],
            'classSchoolYearId' => ['nullable', 'integer', Rule::exists('school_years', 'id')],
        ]);

        $exists = SchoolClass::query()
            ->where('school_year_id', $data['classSchoolYearId'] ?: null)
            ->where('name', $data['className'])
            ->exists();

        if ($exists) {
            $this->addError('className', 'Rombel dengan nama yang sama pada tahun ajaran ini sudah ada.');
            $this->notifyError('Rombel dengan nama ini sudah ada di tahun ajaran yang dipilih.');

            return;
        }

        SchoolClass::query()->create([
            'name' => $data['className'],
            'grade_level' => $data['classGrade'] ?: null,
            'major' => $data['classMajor'] ?: null,
            'school_year_id' => $data['classSchoolYearId'] ?: null,
        ]);

        $this->closeCreateClassModal();
        session()->flash('status', 'class-created');
        $this->notifySuccess('Rombel baru berhasil ditambahkan.');
    }

    public function openEditClassModal(int $classId): void
    {
        $class = SchoolClass::query()->findOrFail($classId);
        $this->editingClassId = $class->id;
        $this->editClassName = (string) $class->name;
        $this->editClassGrade = (string) ($class->grade_level ?? '');
        $this->editClassMajor = (string) ($class->major ?? '');
        $this->editClassSchoolYearId = (string) ($class->school_year_id ?? '');
        $this->showEditClassModal = true;
    }

    public function closeEditClassModal(): void
    {
        $this->showEditClassModal = false;
        $this->editingClassId = null;
    }

    public function updateClass(): void
    {
        if ($this->editingClassId === null) {
            return;
        }

        $data = $this->validateWithFriendlyMessage([
            'editClassName' => ['required', 'string', 'max:64'],
            'editClassGrade' => ['nullable', 'integer', 'min:1', 'max:12'],
            'editClassMajor' => ['nullable', 'string', 'max:64'],
            'editClassSchoolYearId' => ['nullable', 'integer', Rule::exists('school_years', 'id')],
        ]);

        $exists = SchoolClass::query()
            ->whereKeyNot($this->editingClassId)
            ->where('school_year_id', $data['editClassSchoolYearId'] ?: null)
            ->where('name', $data['editClassName'])
            ->exists();

        if ($exists) {
            $this->addError('editClassName', 'Rombel dengan nama yang sama pada tahun ajaran ini sudah ada.');
            $this->notifyError('Nama rombel ini sudah dipakai di tahun ajaran yang sama.');

            return;
        }

        SchoolClass::query()->whereKey($this->editingClassId)->update([
            'name' => $data['editClassName'],
            'grade_level' => $data['editClassGrade'] ?: null,
            'major' => $data['editClassMajor'] ?: null,
            'school_year_id' => $data['editClassSchoolYearId'] ?: null,
        ]);

        $this->closeEditClassModal();
        session()->flash('status', 'class-updated');
        $this->notifySuccess('Data rombel berhasil diperbarui.');
    }

    public function openSyncModal(?int $classId = null): void
    {
        $this->syncClassId = $classId;
        $this->studentsRaw = '';
        $this->studentsFile = null;
        $this->resetValidation();
        $this->showSyncModal = true;
    }

    public function closeSyncModal(): void
    {
        $this->showSyncModal = false;
        $this->syncClassId = null;
        $this->studentsRaw = '';
        $this->studentsFile = null;
    }

    public function syncStudents(): void
    {
        $this->validateWithFriendlyMessage([
            'syncClassId' => ['required', 'integer', Rule::exists('school_classes', 'id')],
            'studentsRaw' => ['nullable', 'string'],
            'studentsFile' => ['nullable', 'file', 'mimes:csv,txt,xls,xlsx'],
        ]);

        if (trim($this->studentsRaw) === '' && $this->studentsFile === null) {
            $this->addError('studentsRaw', 'Isi data siswa atau upload file terlebih dahulu.');
            $this->notifyError('Masukkan data siswa dulu atau upload file import.');

            return;
        }

        $rawPayload = trim($this->studentsRaw);
        if ($this->studentsFile !== null) {
            $rawPayload = $this->extractRowsFromUploadedFile($this->studentsFile);
            if ($rawPayload === '') {
                $this->addError('studentsFile', 'File tidak berisi baris data siswa yang valid.');
                $this->notifyError('File belum bisa diproses. Pastikan format file sesuai template.');

                return;
            }
        }

        $class = SchoolClass::query()->findOrFail($this->syncClassId);
        $result = app(ClassStudentSyncService::class)->syncFromRawRows($class, $rawPayload);
        $message = 'Sync siswa selesai. Created: '.((int) $result['created']).', Updated: '.((int) $result['updated']).', Skipped: '.((int) $result['skipped']).'.';
        session()->flash('status', $message);
        if (! empty($result['errors'])) {
            $this->addError('studentsRaw', implode(' ', array_slice((array) $result['errors'], 0, 3)));
            $this->notifyError('Import selesai sebagian. Ada beberapa baris yang perlu diperbaiki.');

            return;
        }

        $this->notifySuccess('Data siswa berhasil diimport dan disinkronkan.');
        $this->closeSyncModal();
    }

    protected function extractRowsFromUploadedFile(TemporaryUploadedFile $file): string
    {
        $extension = strtolower((string) $file->getClientOriginalExtension());

        if (in_array($extension, ['csv', 'txt'], true)) {
            $content = file_get_contents($file->getRealPath());
            $lines = preg_split('/\r\n|\r|\n/', trim((string) $content)) ?: [];
            $normalized = [];
            foreach ($lines as $index => $line) {
                $value = trim((string) $line);
                if ($value === '') {
                    continue;
                }

                $first = strtolower(trim((string) explode('|', $value)[0]));
                if ($index === 0 && in_array($first, ['nis', 'nisn'], true)) {
                    continue;
                }

                $normalized[] = $value;
            }

            return implode("\n", $normalized);
        }

        $sheets = Excel::toArray(new StudentSyncRowsImport, $file);
        $lines = [];
        foreach ($sheets as $sheet) {
            foreach ($sheet as $rowIndex => $row) {
                $values = collect((array) $row)
                    ->map(fn ($value) => trim((string) $value))
                    ->filter(fn ($value) => $value !== '')
                    ->values()
                    ->all();

                if (empty($values)) {
                    continue;
                }

                if (
                    $rowIndex === 0
                    && isset($values[0])
                    && in_array(strtolower($values[0]), ['nis', 'nisn'], true)
                ) {
                    continue;
                }

                $lines[] = implode('|', $values);
            }
        }

        return implode("\n", $lines);
    }

    public function render()
    {
        $lookupTtl = now()->addMinutes(5);

        return view('livewire.admin.classes-index-table', [
            'classes' => SchoolClass::query()
                ->with('schoolYear:id,name')
                ->withCount([
                    'students as students_count' => fn ($query) => $query->where('role', User::ROLE_STUDENT),
                ])
                ->orderBy('grade_level')
                ->orderBy('name')
                ->paginate(12),
            'classOptions' => Cache::remember('lw:classes:options:v1', $lookupTtl, fn () => SchoolClass::query()
                ->with('schoolYear:id,name')
                ->orderBy('grade_level')
                ->orderBy('name')
                ->get(['id', 'name', 'school_year_id'])),
            'schoolYears' => Cache::remember('lw:classes:school-years:v1', $lookupTtl, fn () => SchoolYear::query()
                ->with(['semesters' => fn ($q) => $q->orderBy('semester_number')])
                ->orderByDesc('is_active')
                ->orderByDesc('start_date')
                ->get(['id', 'name', 'start_date', 'end_date', 'is_active'])),
        ]);
    }
}
