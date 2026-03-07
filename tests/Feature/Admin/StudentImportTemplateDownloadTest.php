<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentImportTemplateDownloadTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_download_student_import_template_as_xlsx_and_csv(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $this->actingAs($admin)
            ->get(route('admin.classes.students.template', ['format' => 'xlsx']))
            ->assertOk()
            ->assertHeader('content-disposition', 'attachment; filename=template-import-siswa.xlsx');

        $this->actingAs($admin)
            ->get(route('admin.classes.students.template', ['format' => 'csv']))
            ->assertOk()
            ->assertHeader('content-disposition', 'attachment; filename=template-import-siswa.csv');
    }

    public function test_non_admin_cannot_download_student_import_template(): void
    {
        $teacher = User::factory()->create(['role' => User::ROLE_TEACHER]);

        $this->actingAs($teacher)
            ->get(route('admin.classes.students.template', ['format' => 'xlsx']))
            ->assertStatus(403);
    }
}

