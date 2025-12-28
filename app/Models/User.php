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

    /**
     * Check if user is admin
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Check if user is mentor
     */
    public function isMentor(): bool
    {
        return $this->role === 'mentor';
    }

    /**
     * Check if user is intern
     */
    public function isIntern(): bool
    {
        return $this->role === 'intern';
    }

    /**
     * Check if user is active
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Get the dashboard URL based on user role
     */
    public function getDashboardUrl(): string
    {
        return match ($this->role) {
            'admin' => '/admin/dashboard',
            'mentor' => '/mentor/dashboard',
            'intern' => '/intern/dashboard',
            default => '/',
        };
    }

    // ========================================
    // RELATIONSHIPS
    // ========================================

    /**
     * Internships where user is the intern
     */
    public function internshipsAsIntern(): HasMany
    {
        return $this->hasMany(Internship::class, 'intern_id');
    }

    /**
     * Internships where user is the mentor
     */
    public function internshipsAsMentor(): HasMany
    {
        return $this->hasMany(Internship::class, 'mentor_id');
    }

    /**
     * User's attendance presences
     */
    public function presences(): HasMany
    {
        return $this->hasMany(Presence::class);
    }

    /**
     * Tasks assigned to user (as intern)
     */
    public function assignedTasks(): HasMany
    {
        return $this->hasMany(Task::class, 'intern_id');
    }

    /**
     * Tasks created by user (as mentor)
     */
    public function createdTasks(): HasMany
    {
        return $this->hasMany(Task::class, 'mentor_id');
    }

    /**
     * User's logbook entries
     */
    public function logbooks(): HasMany
    {
        return $this->hasMany(Logbook::class);
    }

    /**
     * Applicants reviewed by this user
     */
    public function reviewedApplicants(): HasMany
    {
        return $this->hasMany(Applicant::class, 'reviewed_by');
    }

    /**
     * Audit logs for this user
     */
    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }

    /**
     * Get current active internship (for interns)
     */
    public function currentInternship()
    {
        return $this->internshipsAsIntern()
            ->where('status', 'active')
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->first();
    }

    /**
     * Get assigned mentor (for interns)
     */
    public function getMentor()
    {
        $internship = $this->currentInternship();
        return $internship ? $internship->mentor : null;
    }

    /**
     * Get assigned interns (for mentors)
     */
    public function getInterns()
    {
        return User::whereIn('id', function ($query) {
            $query->select('intern_id')
                ->from('internships')
                ->where('mentor_id', $this->id)
                ->where('status', 'active');
        })->get();
    }
}
