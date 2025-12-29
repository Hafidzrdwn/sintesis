<?php

namespace App\Traits;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

/**
 * Auditable Trait
 * 
 * This trait automatically logs all changes to models that use it.
 * It captures who made the change, what was changed, and when.
 */
trait Auditable
{
    public static function bootAuditable(): void
    {
        static::created(function (Model $model) {
            static::logAudit($model, AuditLog::ACTION_CREATED, null, $model->getAttributes());
        });

        static::updated(function (Model $model) {
            $oldValues = $model->getOriginal();
            $newValues = $model->getChanges();
            
            unset($newValues['updated_at']);
            
            if (!empty($newValues)) {
                $relevantOldValues = array_intersect_key($oldValues, $newValues);
                static::logAudit($model, AuditLog::ACTION_UPDATED, $relevantOldValues, $newValues);
            }
        });

        static::deleted(function (Model $model) {
            static::logAudit($model, AuditLog::ACTION_DELETED, $model->getAttributes(), null);
        });

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
        if (app()->runningInConsole() && !Auth::check()) {
            return;
        }

        if ($model instanceof AuditLog) {
            return;
        }

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
