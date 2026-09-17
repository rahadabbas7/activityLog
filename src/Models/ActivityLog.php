<?php

namespace Rahad\ActivityLog\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Auth;

class ActivityLog extends Model
{
    use MassPrunable;

    protected $guarded = [];

    /**
     * Get the table associated with the model.
     */
    public function getTable(): string
    {
        return config('activitylog.table_name', parent::getTable() ?: 'activity_logs');
    }

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'old_values' => 'array',
            'new_values' => 'array',
            'metadata' => 'array',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Prune records older than configured days.
     */
    public function prunable(): Builder
    {
        $days = config('activitylog.delete_records_older_than_days');

        if (! $days) {
            return static::whereRaw('1 = 0');
        }

        return static::where('created_at', '<=', now()->subDays($days));
    }

    /**
     * Causer polymorphic relation (who performed the action).
     */
    public function causer(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Subject polymorphic relation (the affected model).
     */
    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    /*
    |--------------------------------------------------------------------------
    | Query Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeModule(Builder $query, string $module): Builder
    {
        return $query->where('module', $module);
    }

    public function scopeAction(Builder $query, string $action): Builder
    {
        return $query->where('action', $action);
    }

    public function scopeRole(Builder $query, string $role): Builder
    {
        return $query->where('role', $role);
    }

    public function scopeToday(Builder $query): Builder
    {
        return $query->whereDate('created_at', Carbon::today());
    }

    public function scopeDate(Builder $query, string $date): Builder
    {
        return $query->whereDate('created_at', $date);
    }

    public function scopeBetween(Builder $query, string $from, string $to): Builder
    {
        return $query->whereBetween('created_at', [$from, $to . ' 23:59:59']);
    }

    public function scopeCausedBy(Builder $query, Model $causer): Builder
    {
        return $query->where('causer_type', get_class($causer))
            ->where('causer_id', $causer->getKey());
    }

    public function scopeForSubject(Builder $query, Model $subject): Builder
    {
        return $query->where('subject_type', get_class($subject))
            ->where('subject_id', $subject->getKey());
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    */

    public function getCauserNameAttribute(): string
    {
        return $this->causer?->name
            ?? $this->causer?->email
            ?? 'System';
    }

    public function getSubjectNameAttribute(): string
    {
        return $this->subject?->name
            ?? $this->subject?->title
            ?? ($this->subject_type ? class_basename($this->subject_type) : '—');
    }

    /*
    |--------------------------------------------------------------------------
    | Static Logging Helper Methods
    |--------------------------------------------------------------------------
    */

    /**
     * Record a new activity log entry.
     */
    public static function record(
        string $title,
        string $module,
        string $action,
        ?Model $subject = null,
        array $old = [],
        array $new = [],
        array $metadata = [],
        string $logName = 'default'
    ): ?static {
        if (! config('activitylog.enabled', true)) {
            return null;
        }

        $causerResolver = config('activitylog.causer.resolver');
        $user = is_callable($causerResolver) ? $causerResolver() : Auth::user();

        $roleResolver = config('activitylog.causer.role_resolver');
        if (is_callable($roleResolver)) {
            $role = $roleResolver($user);
        } else {
            $role = $user?->roles?->first()?->name ?? $user?->role ?? null;
        }

        $trackRequest = config('activitylog.request', []);
        $req = request();

        return static::create([
            'log_name' => $logName,
            'title' => $title,
            'module' => $module,
            'action' => $action,

            // Causer
            'causer_type' => $user ? get_class($user) : null,
            'causer_id' => $user?->getKey(),

            // Subject
            'subject_type' => $subject ? get_class($subject) : null,
            'subject_id' => $subject?->getKey(),

            // Role
            'role' => $role,

            // Values
            'old_values' => empty($old) ? null : $old,
            'new_values' => empty($new) ? null : $new,
            'metadata' => empty($metadata) ? null : $metadata,

            // Request Info
            'ip_address' => ($trackRequest['log_ip_address'] ?? true) ? $req?->ip() : null,
            'method' => ($trackRequest['log_method'] ?? true) ? $req?->method() : null,
            'url' => ($trackRequest['log_url'] ?? true) ? $req?->fullUrl() : null,
            'user_agent' => ($trackRequest['log_user_agent'] ?? true) ? $req?->userAgent() : null,
        ]);
    }

    /**
     * Log a 'created' event.
     */
    public static function recordCreated(
        string $module,
        Model $subject,
        array $new = [],
        array $metadata = []
    ): ?static {
        return static::record(
            title: class_basename($subject) . ' created',
            module: $module,
            action: 'created',
            subject: $subject,
            new: $new,
            metadata: $metadata
        );
    }

    /**
     * Log an 'updated' event.
     */
    public static function recordUpdated(
        string $module,
        Model $subject,
        array $old = [],
        array $new = [],
        array $metadata = []
    ): ?static {
        return static::record(
            title: class_basename($subject) . ' updated',
            module: $module,
            action: 'updated',
            subject: $subject,
            old: $old,
            new: $new,
            metadata: $metadata
        );
    }

    /**
     * Log a 'deleted' event.
     */
    public static function recordDeleted(
        string $module,
        Model $subject,
        array $old = [],
        array $metadata = []
    ): ?static {
        return static::record(
            title: class_basename($subject) . ' deleted',
            module: $module,
            action: 'deleted',
            subject: $subject,
            old: $old,
            metadata: $metadata
        );
    }

    /**
     * Log a custom event.
     */
    public static function recordCustom(
        string $title,
        string $module,
        string $action,
        ?Model $subject = null,
        array $metadata = []
    ): ?static {
        return static::record(
            title: $title,
            module: $module,
            action: $action,
            subject: $subject,
            metadata: $metadata
        );
    }
}
