<?php

namespace Tests\Feature\Admin;

use App\Livewire\Admin\ClassesIndexTable;
use App\Models\SchoolClass;
use App\Models\SchoolYear;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Livewire\Livewire;
use Tests\TestCase;

class StudentImportFileSyncTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_imports_students_from_uploaded_csv_file(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $year = SchoolYear::query()->create([
            'name' => '2025/2026',
            'is_active' => true,
        ]);
        $class = SchoolClass::query()->create([
            'name' => '7A',
            'grade_level' => 7,
            'school_year_id' => $year->id,
        ]);

        $csv = implode("\n", [
            'NISN|Nama Lengkap|NIK|Tempat Lahir|Tanggal Lahir|Rombel|Tingkat|Nama Wali',
            '32010001|Budi Santoso|3201000101010001|Bandung|2013-05-01|7A|7|Rina',
        ]);
        $file = UploadedFile::fake()->createWithContent('students.csv', $csv);

        Livewire::actingAs($admin)
            ->test(ClassesIndexTable::class)
            ->set('syncClassId', $class->id)
            ->set('studentsFile', $file)
            ->call('syncStudents')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('users', [
            'role' => User::ROLE_STUDENT,
            'class_id' => $class->id,
            'nisn' => '32010001',
            'nik' => '3201000101010001',
            'birth_place' => 'Bandung',
            'guardian_name' => 'Rina',
        ]);
    }
}

