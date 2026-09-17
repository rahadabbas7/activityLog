<?php

namespace Rahat\ActivityLog\Concerns;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Rahat\ActivityLog\Models\ActivityLog;

trait HasActivityLogs
{
    /**
     * Get all activities where this model was the causer.
     */
    public function activitiesAsCauser(): MorphMany
    {
        $modelClass = config('activitylog.model', ActivityLog::class);

        return $this->morphMany($modelClass, 'causer');
    }

    /**
     * Get all activities where this model was the subject.
     */
    public function activitiesAsSubject(): MorphMany
    {
        $modelClass = config('activitylog.model', ActivityLog::class);

        return $this->morphMany($modelClass, 'subject');
    }
}
