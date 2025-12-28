<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Applicant extends Model
{
    use HasFactory, HasUuids, SoftDeletes, Auditable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'full_name',
        'email',
        'phone',
        'birth_date',
        'gender',
        'address',
        'university',
        'major',
        'semester',
        'position_applied',
        'cv_path',
        'portfolio_url',
        'motivation',
        'status',
        'notes',
        'reviewed_by',
        'reviewed_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'reviewed_at' => 'datetime',
        ];
    }

    /**
     * Status options for dropdown
     */
    public static function statusOptions(): array
    {
        return [
            'pending' => 'Pending',
            'reviewed' => 'Reviewed',
            'interview' => 'Interview',
            'accepted' => 'Accepted',
            'rejected' => 'Rejected',
        ];
    }

    /**
     * Check if applicant is pending
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if applicant is accepted
     */
    public function isAccepted(): bool
    {
        return $this->status === 'accepted';
    }

    /**
     * Check if applicant is rejected
     */
    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    // ========================================
    // RELATIONSHIPS
    // ========================================

    /**
     * The user who reviewed this applicant
     */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * The internship created from this application
     */
    public function internship()
    {
        return $this->hasOne(Internship::class);
    }
}
