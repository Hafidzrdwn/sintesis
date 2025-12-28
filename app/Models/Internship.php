<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Internship extends Model
{
    use HasFactory, HasUuids, SoftDeletes, Auditable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'intern_id',
        'mentor_id',
        'applicant_id',
        'position',
        'department',
        'start_date',
        'end_date',
        'status',
        'notes',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }

    /**
     * Status options for dropdown
     */
    public static function statusOptions(): array
    {
        return [
            'active' => 'Active',
            'completed' => 'Completed',
            'terminated' => 'Terminated',
            'extended' => 'Extended',
        ];
    }

    /**
     * Check if internship is active
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Check if internship is current (within date range)
     */
    public function isCurrent(): bool
    {
        return $this->isActive() 
            && $this->start_date <= now() 
            && $this->end_date >= now();
    }

    /**
     * Get remaining days of internship
     */
    public function getRemainingDays(): int
    {
        if (!$this->isCurrent()) {
            return 0;
        }
        return now()->diffInDays($this->end_date, false);
    }

    /**
     * Get progress percentage of internship
     */
    public function getProgressPercentage(): float
    {
        $totalDays = $this->start_date->diffInDays($this->end_date);
        if ($totalDays === 0) {
            return 100;
        }
        
        $passedDays = $this->start_date->diffInDays(now());
        return min(100, max(0, ($passedDays / $totalDays) * 100));
    }

    // ========================================
    // RELATIONSHIPS
    // ========================================

    /**
     * The intern (user) for this internship
     */
    public function intern(): BelongsTo
    {
        return $this->belongsTo(User::class, 'intern_id');
    }

    /**
     * The mentor (user) for this internship
     */
    public function mentor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'mentor_id');
    }

    /**
     * The applicant that led to this internship
     */
    public function applicant(): BelongsTo
    {
        return $this->belongsTo(Applicant::class);
    }

    /**
     * Tasks associated with this internship
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    /**
     * Logbooks associated with this internship
     */
    public function logbooks(): HasMany
    {
        return $this->hasMany(Logbook::class);
    }
}
