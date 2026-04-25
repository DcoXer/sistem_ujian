<?php

namespace Tests\Feature\Teacher;

use App\Models\Exam;
use App\Models\ExamOption;
use App\Models\ExamQuestion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class QuestionManagementUiTest extends TestCase
{
    use RefreshDatabase;

    public function test_teacher_can_see_tambah_soal_button_within_authoring_window(): void
    {
        Carbon::setTestNow(now());

        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $teacher = User::factory()->create(['role' => User::ROLE_TEACHER]);

        $exam = Exam::create([
            'title' => 'Ujian IPA Kelas 4',
            'start_at' => now()->addDay(),
            'end_at' => now()->addDays(2),
            'authoring_start_at' => now()->subHour(),
            'authoring_end_at' => now()->addHours(3),
            'duration_minutes' => 60,
            'status' => Exam::STATUS_DRAFT,
            'created_by' => $admin->id,
            'author_id' => $teacher->id,
            'teacher_id' => $teacher->id,
        ]);

        $this->actingAs($teacher)
            ->get(route('teacher.exams.show', $exam))
            ->assertOk()
            ->assertSee('Ujian IPA Kelas 4')
            ->assertSee('Batas buat soal')
            ->assertSee('Pastikan semua soal sudah diisi sebelum batas waktu');
    }

    public function test_teacher_sees_read_only_message_after_exam_finished(): void
    {
        Carbon::setTestNow(now());

        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $teacher = User::factory()->create(['role' => User::ROLE_TEACHER]);

        $exam = Exam::create([
            'title' => 'Ujian Sejarah',
            'start_at' => now()->subHours(3),
            'end_at' => now()->subHours(2),
            'authoring_start_at' => now()->subDays(2),
            'authoring_end_at' => now()->subDay(),
            'duration_minutes' => 60,
            'status' => Exam::STATUS_FINISHED,
            'created_by' => $admin->id,
            'author_id' => $teacher->id,
            'teacher_id' => $teacher->id,
        ]);

        $this->actingAs($teacher)
            ->get(route('teacher.exams.show', $exam))
            ->assertOk()
            ->assertSee('Soal ditampilkan hanya untuk dibaca')
            // Modal "Tambah Soal" tidak dirender saat read-only (hanya ada saat canManageQuestions=true)
            ->assertDontSee('id="open-question-modal"', false);
    }

    public function test_question_management_page_shows_pilihan_abcd_labels_for_correct_answer(): void
    {
        Carbon::setTestNow(now());

        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $teacher = User::factory()->create(['role' => User::ROLE_TEACHER]);

        $exam = Exam::create([
            'title' => 'Ujian Matematika',
            'start_at' => now()->addDay(),
            'end_at' => now()->addDays(2),
            'authoring_start_at' => now()->subHour(),
            'authoring_end_at' => now()->addHours(3),
            'duration_minutes' => 60,
            'status' => Exam::STATUS_DRAFT,
            'created_by' => $admin->id,
            'author_id' => $teacher->id,
        ]);

        $question = ExamQuestion::create([
            'exam_id' => $exam->id,
            'question_text' => '2 + 2 = ?',
            'points' => 10,
            'order' => 1,
        ]);

        foreach (['3', '4', '5', '6'] as $index => $text) {
            ExamOption::create([
                'exam_question_id' => $question->id,
                'option_text' => $text,
                'is_correct' => $index === 1,
            ]);
        }

        $response = $this->actingAs($teacher)
            ->get(route('teacher.exams.show', $exam))
            ->assertOk();

        // Jawaban benar sekarang pakai radio A/B/C/D, bukan input angka
        $response->assertSee('Pilihan A')
            ->assertSee('Pilihan B')
            ->assertSee('Pilihan C')
            ->assertSee('Pilihan D');

        // Tidak ada lagi label teknis lama
        $response->assertDontSee('Index jawaban benar');
        $response->assertDontSee('0=A, 1=B');
    }

    public function test_question_list_shows_benar_badge_on_correct_option(): void
    {
        Carbon::setTestNow(now());

        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $teacher = User::factory()->create(['role' => User::ROLE_TEACHER]);

        $exam = Exam::create([
            'title' => 'Ujian PKN',
            'start_at' => now()->subHours(3),
            'end_at' => now()->subHours(2),
            'authoring_start_at' => now()->subDays(2),
            'authoring_end_at' => now()->subDay(),
            'duration_minutes' => 60,
            'status' => Exam::STATUS_FINISHED,
            'created_by' => $admin->id,
            'author_id' => $teacher->id,
        ]);

        $question = ExamQuestion::create([
            'exam_id' => $exam->id,
            'question_text' => 'Siapa pendiri BPUPKI?',
            'points' => 10,
            'order' => 1,
        ]);

        ExamOption::create(['exam_question_id' => $question->id, 'option_text' => 'Soekarno', 'is_correct' => false]);
        ExamOption::create(['exam_question_id' => $question->id, 'option_text' => 'Dr. Radjiman', 'is_correct' => true]);

        $this->actingAs($teacher)
            ->get(route('teacher.exams.show', $exam))
            ->assertOk()
            ->assertSee('(Benar)');
    }

    public function test_question_management_page_shows_authoring_window_deadline(): void
    {
        Carbon::setTestNow(now());

        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $teacher = User::factory()->create(['role' => User::ROLE_TEACHER]);

        $exam = Exam::create([
            'title' => 'Ujian Geografi',
            'start_at' => now()->addDay(),
            'end_at' => now()->addDays(2),
            'authoring_start_at' => now()->subHour(),
            'authoring_end_at' => now()->addHours(3),
            'duration_minutes' => 60,
            'status' => Exam::STATUS_DRAFT,
            'created_by' => $admin->id,
            'author_id' => $teacher->id,
        ]);

        $this->actingAs($teacher)
            ->get(route('teacher.exams.show', $exam))
            ->assertOk()
            ->assertSee('Batas buat soal');
    }

    public function test_teacher_cannot_access_show_page_when_outside_authoring_window(): void
    {
        Carbon::setTestNow(now());

        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $teacher = User::factory()->create(['role' => User::ROLE_TEACHER]);

        // Authoring window belum dimulai → update policy false, viewAuthoredQuestions false → 403
        $exam = Exam::create([
            'title' => 'Ujian Seni',
            'start_at' => now()->addDays(3),
            'end_at' => now()->addDays(4),
            'authoring_start_at' => now()->addDay(),
            'authoring_end_at' => now()->addDays(2),
            'duration_minutes' => 60,
            'status' => Exam::STATUS_DRAFT,
            'created_by' => $admin->id,
            'author_id' => $teacher->id,
            'teacher_id' => $teacher->id,
        ]);

        $this->actingAs($teacher)
            ->get(route('teacher.exams.show', $exam))
            ->assertForbidden();
    }
}
