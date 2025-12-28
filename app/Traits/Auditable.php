<?php

namespace App\Traits;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

/**
 * Auditable Trait
 * 
 * KSI (Keamanan Sistem Informasi) Compliance:
 * This trait automatically logs all changes to models that use it.
 * It captures who made the change, what was changed, and when.
 */
trait Auditable
{
    /**
     * Boot the auditable trait
     */
    public static function bootAuditable(): void
    {
        // Log when a model is created
        static::created(function (Model $model) {
            static::logAudit($model, AuditLog::ACTION_CREATED, null, $model->getAttributes());
        });

        // Log when a model is updated
        static::updated(function (Model $model) {
            $oldValues = $model->getOriginal();
            $newValues = $model->getChanges();
            
            // Remove timestamps from changes to reduce noise
            unset($newValues['updated_at']);
            
            if (!empty($newValues)) {
                // Only include old values for fields that changed
                $relevantOldValues = array_intersect_key($oldValues, $newValues);
                static::logAudit($model, AuditLog::ACTION_UPDATED, $relevantOldValues, $newValues);
            }
        });

        // Log when a model is deleted
        static::deleted(function (Model $model) {
            static::logAudit($model, AuditLog::ACTION_DELETED, $model->getAttributes(), null);
        });

        // Log when a model is restored (if using SoftDeletes)
        if (method_exists(static::class, 'restored')) {
            static::restored(function (Model $model) {
                static::logAudit($model, AuditLog::ACTION_RESTORED, null, $model->getAttributes());
            });
        }
    }

    /**
     * Create an audit log entry
     */
    protected static function logAudit(
        Model $model, 
        string $action, 
        ?array $oldValues, 
        ?array $newValues,
        ?array $metadata = null
    ): void {
        // Skip if in console and no user (e.g., during migrations/seeding)
        if (app()->runningInConsole() && !Auth::check()) {
            return;
        }

        // Don't log changes to audit_logs table itself (prevent infinite loop)
        if ($model instanceof AuditLog) {
            return;
        }

        // Filter out sensitive fields
        $sensitiveFields = ['password', 'remember_token', 'google_id'];
        $oldValues = static::filterSensitiveFields($oldValues, $sensitiveFields);
        $newValues = static::filterSensitiveFields($newValues, $sensitiveFields);

        try {
            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => $action,
                'auditable_type' => get_class($model),
                'auditable_id' => $model->getKey(),
                'old_values' => $oldValues,
                'new_values' => $newValues,
                'metadata' => $metadata,
                'ip_address' => Request::ip(),
                'user_agent' => Request::userAgent(),
                'url' => Request::fullUrl(),
                'method' => Request::method(),
            ]);
        } catch (\Exception $e) {
            // Silently fail to prevent disrupting main operations
            // In production, you might want to log this to a separate error log
            report($e);
        }
    }

    /**
     * Filter sensitive fields from values array
     */
    protected static function filterSensitiveFields(?array $values, array $sensitiveFields): ?array
    {
        if ($values === null) {
            return null;
        }

        foreach ($sensitiveFields as $field) {
            if (isset($values[$field])) {
                $values[$field] = '[REDACTED]';
            }
        }

        return $values;
    }

    /**
     * Log a custom action for this model
     */
    public function logCustomAction(string $action, ?array $metadata = null): void
    {
        static::logAudit($this, $action, null, null, $metadata);
    }

    /**
     * Get all audit logs for this model
     */
    public function auditLogs()
    {
        return AuditLog::where('auditable_type', get_class($this))
            ->where('auditable_id', $this->getKey())
            ->orderBy('created_at', 'desc');
    }
}
