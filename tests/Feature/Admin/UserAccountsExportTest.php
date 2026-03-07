<?php

namespace Tests\Feature\Admin;

use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserAccountsExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_export_non_student_accounts(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        User::factory()->create(['role' => User::ROLE_TEACHER, 'name' => 'Guru Export']);

        $response = $this->actingAs($admin)
            ->get(route('admin.users.export', [
                'role' => User::ROLE_TEACHER,
                'format' => 'csv',
            ]));

        $response->assertOk();
        $this->assertStringContainsString('attachment; filename=akun-teacher-', (string) $response->headers->get('content-disposition'));
    }

    public function test_export_student_requires_class_filter(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $this->actingAs($admin)
            ->from(route('admin.users.index'))
            ->get(route('admin.users.export', [
                'role' => User::ROLE_STUDENT,
                'format' => 'csv',
            ]))
            ->assertRedirect(route('admin.users.index'))
            ->assertSessionHasErrors('export');
    }

    public function test_export_student_is_filtered_per_class(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $classA = SchoolClass::query()->create(['name' => '8A', 'grade_level' => 8]);
        $classB = SchoolClass::query()->create(['name' => '8B', 'grade_level' => 8]);

        User::factory()->create([
            'role' => User::ROLE_STUDENT,
            'class_id' => $classA->id,
            'name' => 'Siswa 8A',
            'nisn' => '32080001',
        ]);
        User::factory()->create([
            'role' => User::ROLE_STUDENT,
            'class_id' => $classB->id,
            'name' => 'Siswa 8B',
            'nisn' => '32080002',
        ]);

        $response = $this->actingAs($admin)
            ->get(route('admin.users.export', [
                'role' => User::ROLE_STUDENT,
                'class_id' => $classA->id,
                'format' => 'csv',
            ]));

        $response->assertOk();
        $filePath = $response->baseResponse->getFile()->getPathname();
        $content = (string) file_get_contents($filePath);
        $this->assertStringContainsString('Siswa 8A', $content);
        $this->assertStringNotContainsString('Siswa 8B', $content);
    }
}
