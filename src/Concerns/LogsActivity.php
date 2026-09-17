<?php

namespace Rahad\ActivityLog\Concerns;

use Illuminate\Database\Eloquent\Model;
use Rahad\ActivityLog\Models\ActivityLog;

trait LogsActivity
{
    /**
     * Boot the trait to listen for model lifecycle events.
     */
    public static function bootLogsActivity(): void
    {
        static::created(function (Model $model) {
            if ($model->shouldLogActivity('created')) {
                $module = $model->getActivityModuleName();
                $attributes = $model->getActivityAttributesToLog($model->getAttributes());
                
                ActivityLog::recordCreated(
                    module: $module,
                    subject: $model,
                    new: $attributes
                );
            }
        });

        static::updated(function (Model $model) {
            if ($model->shouldLogActivity('updated')) {
                $module = $model->getActivityModuleName();
                $dirtyKeys = array_keys($model->getDirty());
                
                $old = [];
                $new = [];

                foreach ($dirtyKeys as $key) {
                    if ($model->isActivityAttributeIgnored($key)) {
                        continue;
                    }

                    $old[$key] = $model->getOriginal($key);
                    $new[$key] = $model->getAttribute($key);
                }

                if (! empty($new)) {
                    ActivityLog::recordUpdated(
                        module: $module,
                        subject: $model,
                        old: $old,
                        new: $new
                    );
                }
            }
        });

        static::deleted(function (Model $model) {
            if ($model->shouldLogActivity('deleted')) {
                $module = $model->getActivityModuleName();
                $attributes = $model->getActivityAttributesToLog($model->getAttributes());

                ActivityLog::recordDeleted(
                    module: $module,
                    subject: $model,
                    old: $attributes
                );
            }
        });

        if (method_exists(static::class, 'restored')) {
            static::restored(function (Model $model) {
                if ($model->shouldLogActivity('restored')) {
                    $module = $model->getActivityModuleName();

                    ActivityLog::recordCustom(
                        title: class_basename($model) . ' restored',
                        module: $module,
                        action: 'restored',
                        subject: $model
                    );
                }
            });
        }
    }

    /**
     * Determine if an action should be logged.
     */
    public function shouldLogActivity(string $action): bool
    {
        return true;
    }

    /**
     * Get the module name to use in logs.
     */
    public function getActivityModuleName(): string
    {
        return property_exists($this, 'activityModule')
            ? $this->activityModule
            : class_basename($this);
    }

    /**
     * Get list of ignored attributes from logging.
     */
    public function getActivityIgnoredAttributes(): array
    {
        $defaultIgnored = ['updated_at', 'remember_token', 'password'];

        if (property_exists($this, 'activityIgnoredAttributes')) {
            return array_merge($defaultIgnored, $this->activityIgnoredAttributes);
        }

        return $defaultIgnored;
    }

    /**
     * Determine if an attribute is ignored.
     */
    public function isActivityAttributeIgnored(string $key): bool
    {
        return in_array($key, $this->getActivityIgnoredAttributes(), true);
    }

    /**
     * Filter out ignored attributes from raw attributes array.
     */
    public function getActivityAttributesToLog(array $attributes): array
    {
        $ignored = $this->getActivityIgnoredAttributes();

        return array_diff_key($attributes, array_flip($ignored));
    }
}
