<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasUuids, SoftDeletes, HasRoles, Auditable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'email_verified_at', // Required for Google OAuth auto-verification
        'phone',
        'password',
        'google_id',
        'avatar',
        'role',
        'status',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'google_id',
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
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isMentor(): bool
    {
        return $this->role === 'mentor';
    }

    public function isIntern(): bool
    {
        return $this->role === 'intern';
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function getDashboardUrl(): string
    {
        return match ($this->role) {
            'admin' => '/admin/dashboard',
            'mentor' => '/mentor/dashboard',
            'intern' => '/intern/dashboard',
            default => '/',
        };
    }

    // user sudah terdaftar jadi anak magang, 
    // status: active, completed, terminated, extended
    public function internshipsAsIntern(): HasMany
    {
        return $this->hasMany(Internship::class, 'intern_id');
    }

    public function presences(): HasMany
    {
        return $this->hasMany(Presence::class);
    }

    public function assignedTasks(): HasMany
    {
        return $this->hasMany(Task::class, 'intern_id');
    }

    public function createdTasks(): HasMany
    {
        return $this->hasMany(Task::class, 'mentor_id');
    }

    public function logbooks(): HasMany
    {
        return $this->hasMany(Logbook::class);
    }

    public function reviewedApplicants(): HasMany
    {
        return $this->hasMany(Applicant::class, 'reviewed_by');
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }

    public function currentInternship()
    {
        return $this->internshipsAsIntern()
            ->whereIn('status', ['active', 'extended'])
            ->first();
    }

    public function getMentor()
    {
        $internship = $this->currentInternship();
        return $internship ? $internship->mentor : null;
    }

    public function getInterns()
    {
        return User::whereIn('id', function ($query) {
            $query->select('intern_id')
                ->from('internships')
                ->where('mentor_id', $this->id)
                ->whereIn('status', ['active', 'extended']);
        })->get();
    }

    public function applicant()
    {
        return Applicant::where('user_id', $this->id)->with('job')->latest()->first();
    }

    public function hasActiveInternship(): bool
    {
        return $this->currentInternship() !== null;
    }

    public function getApplicationStatus(): string
    {
        $applicant = $this->applicant();
        
        if (!$applicant) {
            return 'none';
        }

        return match ($applicant->status) {
            'accepted' => 'accepted',
            'rejected' => 'rejected',
            'reviewed' => 'reviewed',
            'interview' => 'interview',
            default => 'pending',
        };
    }

    public function canApply(): bool
    {
        $status = $this->getApplicationStatus();
        $isNotActiveIntern = !$this->hasActiveInternship();

        return in_array($status, ['none', 'rejected']) && $isNotActiveIntern;
    }
}
