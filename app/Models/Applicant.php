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

    protected $fillable = [
        'user_id',
        'job_id',
        // Step 1: Identity
        'full_name',
        'phone',
        'institution_id',
        'referral',
        
        // Step 2: Competencies
        'skills',
        'other_skills',
        'databases',
        'other_databases',
        'operating_systems',
        'other_os',
        
        // Step 3: Interests
        'other_interest',
        'demo_required',
        'self_description',
        'portfolio_url',
        
        // Step 4: Documents
        'start_date',
        'end_date',
        'letter_path',
        'id_card_path',
        'cv_path',
        
        // Meta
        'status',
        'notes',
        'reviewed_by',
        'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'skills' => 'array',
            'databases' => 'array',
            'operating_systems' => 'array',
            'demo_required' => 'boolean',
            'start_date' => 'date',
            'end_date' => 'date',
            'reviewed_at' => 'datetime',
        ];
    }

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

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isAccepted(): bool
    {
        return $this->status === 'accepted';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function internship()
    {
        return $this->hasOne(Internship::class);
    }

    public function job(): BelongsTo
    {
        return $this->belongsTo(Job::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }
}

