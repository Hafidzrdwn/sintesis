<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Logbook extends Model
{
    use HasFactory, HasUuids, SoftDeletes, Auditable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'task_id',
        'internship_id',
        'date',
        'activity',
        'description',
        'duration_hours',
        'status',
        'mentor_notes',
        'approved_by',
        'approved_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'date' => 'date',
            'duration_hours' => 'decimal:2',
            'approved_at' => 'datetime',
        ];
    }

    /**
     * Status options for dropdown
     */
    public static function statusOptions(): array
    {
        return [
            'draft' => 'Draft',
            'submitted' => 'Submitted',
            'approved' => 'Approved',
            'rejected' => 'Rejected',
        ];
    }

    /**
     * Check if logbook is draft
     */
    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    /**
     * Check if logbook is submitted
     */
    public function isSubmitted(): bool
    {
        return $this->status === 'submitted';
    }

    /**
     * Check if logbook is approved
     */
    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    /**
     * Check if logbook is rejected
     */
    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    /**
     * Submit the logbook for review
     */
    public function submit(): void
    {
        $this->update(['status' => 'submitted']);
    }

    /**
     * Approve the logbook
     */
    public function approve(string $approvedBy, string $notes = null): void
    {
        $this->update([
            'status' => 'approved',
            'approved_by' => $approvedBy,
            'approved_at' => now(),
            'mentor_notes' => $notes,
        ]);
    }

    /**
     * Reject the logbook
     */
    public function reject(string $rejectedBy, string $reason): void
    {
        $this->update([
            'status' => 'rejected',
            'approved_by' => $rejectedBy, // Using same field for rejection
            'approved_at' => now(),
            'mentor_notes' => $reason,
        ]);
    }

    // ========================================
    // RELATIONSHIPS
    // ========================================

    /**
     * The user who owns this logbook entry
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The task associated with this logbook entry
     */
    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    /**
     * The internship associated with this logbook entry
     */
    public function internship(): BelongsTo
    {
        return $this->belongsTo(Internship::class);
    }

    /**
     * The user who approved this logbook
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
