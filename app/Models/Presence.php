<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Presence extends Model
{
    use HasFactory, HasUuids, SoftDeletes, Auditable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'date',
        'check_in_time',
        'check_out_time',
        'check_in_latitude',
        'check_in_longitude',
        'check_out_latitude',
        'check_out_longitude',
        'check_in_distance_meters',
        'check_out_distance_meters',
        'attendance_mode',
        'status',
        'check_in_photo',
        'check_out_photo',
        'notes',
        'is_validated',
        'validated_by',
        'validated_at',
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
            'check_in_latitude' => 'decimal:8',
            'check_in_longitude' => 'decimal:8',
            'check_out_latitude' => 'decimal:8',
            'check_out_longitude' => 'decimal:8',
            'check_in_distance_meters' => 'decimal:2',
            'check_out_distance_meters' => 'decimal:2',
            'is_validated' => 'boolean',
            'validated_at' => 'datetime',
        ];
    }

    /**
     * Status options for dropdown
     */
    public static function statusOptions(): array
    {
        return [
            'present' => 'Present',
            'late' => 'Late',
            'absent' => 'Absent',
            'leave' => 'Leave',
            'sick' => 'Sick',
            'permit' => 'Permit',
        ];
    }

    /**
     * Attendance mode options
     */
    public static function attendanceModeOptions(): array
    {
        return [
            'WFO' => 'Work From Office',
            'WFH' => 'Work From Home',
        ];
    }

    /**
     * Check if user has checked in
     */
    public function hasCheckedIn(): bool
    {
        return $this->check_in_time !== null;
    }

    /**
     * Check if user has checked out
     */
    public function hasCheckedOut(): bool
    {
        return $this->check_out_time !== null;
    }

    /**
     * Get working hours duration
     */
    public function getWorkingHours(): ?float
    {
        if (!$this->hasCheckedIn() || !$this->hasCheckedOut()) {
            return null;
        }

        $checkIn = \Carbon\Carbon::parse($this->check_in_time);
        $checkOut = \Carbon\Carbon::parse($this->check_out_time);

        return round($checkIn->diffInMinutes($checkOut) / 60, 2);
    }

    /**
     * Check if check-in location is within allowed radius (for WFO)
     * Default allowed radius is 100 meters
     */
    public function isCheckInLocationValid(float $maxDistance = 100): bool
    {
        if ($this->attendance_mode === 'WFH') {
            return true; // No location validation for WFH
        }

        return $this->check_in_distance_meters !== null 
            && $this->check_in_distance_meters <= $maxDistance;
    }

    // ========================================
    // RELATIONSHIPS
    // ========================================

    /**
     * The user who owns this presence record
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The user who validated this presence
     */
    public function validator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by');
    }
}
