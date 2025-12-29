<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Job extends Model
{
    use HasFactory, HasUuids, SoftDeletes, Auditable;

    /**
     * The table associated with the model.
     */
    protected $table = 'job_listings';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'title',
        'slug',
        'type',
        'status',
        'location',
        'description',
        'responsibilities',
        'requirements',
        'benefits',
        'duration',
        'deadline',
        'image',
    ];

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'responsibilities' => 'array',
            'requirements' => 'array',
            'benefits' => 'array',
            'deadline' => 'date',
        ];
    }

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($job) {
            if (empty($job->slug)) {
                $job->slug = Str::slug($job->title);
            }
        });
    }

    /**
     * Type options for dropdown
     */
    public static function typeOptions(): array
    {
        return [
            'Remote' => 'Remote',
            'Hybrid' => 'Hybrid',
            'On-site' => 'On-site',
        ];
    }

    /**
     * Status options for dropdown
     */
    public static function statusOptions(): array
    {
        return [
            'open' => 'Terbuka',
            'closed' => 'Ditutup',
        ];
    }

    /**
     * Check if job is open
     */
    public function isOpen(): bool
    {
        return $this->status === 'open';
    }

    /**
     * Check if job is closed
     */
    public function isClosed(): bool
    {
        return $this->status === 'closed';
    }

    /**
     * Scope for open jobs
     */
    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }

    /**
     * Scope for closed jobs
     */
    public function scopeClosed($query)
    {
        return $query->where('status', 'closed');
    }

    /**
     * Get status label in Indonesian
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'open' => 'Terbuka',
            'closed' => 'Ditutup',
            default => ucfirst($this->status),
        };
    }

    /**
     * Applicants for this job
     */
    public function applicants(): HasMany
    {
        return $this->hasMany(Applicant::class);
    }
}
