<?php

use Illuminate\Support\Facades\Route;
use Rahad\ActivityLog\Controllers\ActivityLogController;

Route::prefix(config('activitylog.web.route_prefix', 'activity-logs'))
    ->middleware(config('activitylog.web.middleware', ['web', 'auth']))
    ->name('activitylog.')
    ->group(function () {
        Route::get('/', [ActivityLogController::class, 'index'])->name('index');
        Route::get('/{id}', [ActivityLogController::class, 'show'])->name('view');
        Route::delete('/{id}', [ActivityLogController::class, 'destroy'])->name('destroy');
        Route::post('/bulk-delete', [ActivityLogController::class, 'bulkDestroy'])->name('bulk-destroy');
        Route::delete('/date-group/{date}', [ActivityLogController::class, 'destroyDateGroup'])->name('destroy-date-group');
    });
