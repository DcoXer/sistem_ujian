<?php

namespace Tests\Feature\Teacher;

use App\Livewire\Teacher\HomeroomStudentsIndexTable;
use App\Models\HomeroomTeacher;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class HomeroomStudentsTest extends TestCase
{
    use RefreshDatabase;

    public function test_teacher_without_homeroom_assignment_cannot_access_homeroom_students_module(): void
    {
        $teacher = User::factory()->create(['role' => User::ROLE_TEACHER]);

        $this->actingAs($teacher)
            ->get(route('teacher.homeroom.students.index'))
            ->assertForbidden();
    }

    public function test_homeroom_teacher_can_view_and_update_assigned_student(): void
    {
        $teacher = User::factory()->create(['role' => User::ROLE_TEACHER]);
        $class = SchoolClass::query()->create(['name' => '4A', 'grade_level' => 4]);
        HomeroomTeacher::query()->create([
            'teacher_id' => $teacher->id,
            'class_id' => $class->id,
        ]);

        $student = User::factory()->create([
            'role' => User::ROLE_STUDENT,
            'class_id' => $class->id,
            'name' => 'Siswa Lama',
            'nis' => '4001',
            'nisn' => '32000001',
            'nik' => '3200000101010001',
        ]);

        $this->actingAs($teacher)
            ->get(route('teacher.homeroom.students.index'))
            ->assertOk()
            ->assertSee('Siswa Lama');

        Livewire::actingAs($teacher)
            ->test(HomeroomStudentsIndexTable::class)
            ->call('openEditModal', $student->id)
            ->set('name', 'Siswa Baru')
            ->set('guardian_name', 'Ibu Siswa')
            ->set('birth_place', 'Semarang')
            ->set('birth_date', '2014-02-03')
            ->call('saveStudent')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('users', [
            'id' => $student->id,
            'name' => 'Siswa Baru',
            'guardian_name' => 'Ibu Siswa',
            'birth_place' => 'Semarang',
        ]);
    }
}

