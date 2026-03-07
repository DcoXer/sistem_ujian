<?php

namespace Tests\Feature\Admin;

use App\Models\SchoolClass;
use App\Models\SchoolYear;
use App\Models\User;
use App\Services\ClassStudentSyncService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentImportSyncTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_imports_student_profile_fields_from_extended_format(): void
    {
        $year = SchoolYear::query()->create([
            'name' => '2025/2026',
            'is_active' => true,
        ]);

        $class = SchoolClass::query()->create([
            'name' => '6A',
            'grade_level' => 6,
            'school_year_id' => $year->id,
        ]);

        $rows = "32010001|Andi Pratama|3201000101010001|Jakarta|2014-01-01|6A|6|Siti Rahma";
        $result = app(ClassStudentSyncService::class)->syncFromRawRows($class, $rows);

        $this->assertSame(1, $result['created']);
        $this->assertSame(0, $result['updated']);
        $this->assertSame(0, $result['skipped']);

        $student = User::query()->where('nisn', '32010001')->firstOrFail();
        $this->assertSame('Andi Pratama', $student->name);
        $this->assertSame('3201000101010001', $student->nik);
        $this->assertSame('Jakarta', $student->birth_place);
        $this->assertSame('2014-01-01', optional($student->birth_date)->format('Y-m-d'));
        $this->assertSame('Siti Rahma', $student->guardian_name);
        $this->assertSame($class->id, $student->class_id);
        $this->assertSame(User::ROLE_STUDENT, $student->role);
    }
}

