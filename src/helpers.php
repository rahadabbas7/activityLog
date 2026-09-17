<?php

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Rahad\ActivityLog\Models\ActivityLog;

if (! function_exists('activity_log')) {
    /**
     * Record or query activity logs.
     *
     * @param string|null $title
     * @param string|null $module
     * @param string|null $action
     * @param Model|null $subject
     * @param array $metadata
     * @return ActivityLog|Builder|null
     */
    function activity_log(
        ?string $title = null,
        ?string $module = null,
        ?string $action = null,
        ?Model $subject = null,
        array $metadata = []
    ): mixed {
        if ($title === null) {
            $modelClass = config('activitylog.model', ActivityLog::class);
            return $modelClass::query();
        }

        return ActivityLog::recordCustom(
            title: $title,
            module: $module ?? 'General',
            action: $action ?? 'custom',
            subject: $subject,
            metadata: $metadata
        );
    }
}
