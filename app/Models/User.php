<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\URL;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    public const ROLE_ADMIN = 'admin';
    public const ROLE_OPERATOR = 'operator';
    public const ROLE_TEACHER = 'teacher';
    public const ROLE_STUDENT = 'student';
    // Backward compatibility for existing code paths.
    public const ROLE_AUTHOR = self::ROLE_TEACHER;
    public const ROLE_PESERTA = self::ROLE_STUDENT;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'nis',
        'nisn',
        'nik',
        'birth_place',
        'birth_date',
        'guardian_name',
        'class_id',
        'profile_photo_path',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'birth_date' => 'date',
        ];
    }

    protected $appends = [
        'profile_photo_url',
    ];

    public function getProfilePhotoUrlAttribute(): ?string
    {
        if (! $this->profile_photo_path) {
            return null;
        }

        return URL::temporarySignedRoute(
            'profile.photo.show',
            now()->addMinutes(30),
            ['user' => $this->id]
        );
    }

    public function createdExams(): HasMany
    {
        return $this->hasMany(Exam::class, 'created_by');
    }

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function homeroomOfClasses(): HasMany
    {
        return $this->hasMany(HomeroomTeacher::class, 'teacher_id');
    }

    public function teacherSubjects(): HasMany
    {
        return $this->hasMany(TeacherSubject::class, 'teacher_id');
    }

    public function examAttempts(): HasMany
    {
        return $this->hasMany(ExamAttempt::class);
    }

    public function examAttemptAudits(): HasMany
    {
        return $this->hasMany(ExamAttemptAudit::class, 'actor_user_id');
    }
}

