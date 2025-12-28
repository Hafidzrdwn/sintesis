<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * AuditLog Model
 * 
 * KSI (Keamanan Sistem Informasi) Compliance:
 * This model tracks all changes made to sensitive data for audit purposes.
 * It implements polymorphic relationships to track any model in the system.
 */
class AuditLog extends Model
{
    use HasFactory, HasUuids;

    /**
     * Audit logs should never be mass-assigned for security
     * All writes should go through the Auditable trait
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'action',
        'auditable_type',
        'auditable_id',
        'old_values',
        'new_values',
        'metadata',
        'ip_address',
        'user_agent',
        'url',
        'method',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'old_values' => 'array',
            'new_values' => 'array',
            'metadata' => 'array',
        ];
    }

    /**
     * Available action types
     */
    public const ACTION_CREATED = 'created';
    public const ACTION_UPDATED = 'updated';
    public const ACTION_DELETED = 'deleted';
    public const ACTION_RESTORED = 'restored';
    public const ACTION_LOGIN = 'login';
    public const ACTION_LOGOUT = 'logout';
    public const ACTION_LOGIN_FAILED = 'login_failed';
    public const ACTION_PASSWORD_RESET = 'password_reset';
    public const ACTION_EXPORT = 'export';
    public const ACTION_IMPORT = 'import';

    /**
     * Get all available actions
     */
    public static function availableActions(): array
    {
        return [
            self::ACTION_CREATED => 'Created',
            self::ACTION_UPDATED => 'Updated',
            self::ACTION_DELETED => 'Deleted',
            self::ACTION_RESTORED => 'Restored',
            self::ACTION_LOGIN => 'Login',
            self::ACTION_LOGOUT => 'Logout',
            self::ACTION_LOGIN_FAILED => 'Login Failed',
            self::ACTION_PASSWORD_RESET => 'Password Reset',
            self::ACTION_EXPORT => 'Data Export',
            self::ACTION_IMPORT => 'Data Import',
        ];
    }

    /**
     * Get human-readable action label
     */
    public function getActionLabel(): string
    {
        return self::availableActions()[$this->action] ?? ucfirst($this->action);
    }

    /**
     * Get the changes between old and new values
     */
    public function getChanges(): array
    {
        $changes = [];
        $oldValues = $this->old_values ?? [];
        $newValues = $this->new_values ?? [];

        $allKeys = array_unique(array_merge(array_keys($oldValues), array_keys($newValues)));

        foreach ($allKeys as $key) {
            $old = $oldValues[$key] ?? null;
            $new = $newValues[$key] ?? null;

            if ($old !== $new) {
                $changes[$key] = [
                    'old' => $old,
                    'new' => $new,
                ];
            }
        }

        return $changes;
    }

    /**
     * Get shortened model type name
     */
    public function getModelTypeName(): string
    {
        if (!$this->auditable_type) {
            return 'N/A';
        }

        return class_basename($this->auditable_type);
    }

    // ========================================
    // RELATIONSHIPS
    // ========================================

    /**
     * The user who performed the action
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the auditable model (polymorphic)
     */
    public function auditable(): MorphTo
    {
        return $this->morphTo();
    }

    // ========================================
    // SCOPES
    // ========================================

    /**
     * Scope to filter by action type
     */
    public function scopeAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Scope to filter by user
     */
    public function scopeByUser($query, string $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope to filter by model type
     */
    public function scopeForModel($query, string $modelClass)
    {
        return $query->where('auditable_type', $modelClass);
    }

    /**
     * Scope to filter by date range
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Scope for recent logs
     */
    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }
}
