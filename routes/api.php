<?php

use Illuminate\Support\Facades\Route;
use Rahad\ActivityLog\Controllers\Api\ActivityLogApiController;

Route::prefix(config('activitylog.api.route_prefix', 'api/activity-logs'))
    ->middleware(config('activitylog.api.middleware', ['api']))
    ->group(function () {
        Route::get('/', [ActivityLogApiController::class, 'index'])->name('api.activitylog.index');
        Route::get('/date-groups', [ActivityLogApiController::class, 'dateGroups'])->name('api.activitylog.date-groups');
        Route::get('/filter-options', [ActivityLogApiController::class, 'filterOptions'])->name('api.activitylog.filter-options');
        Route::get('/{id}', [ActivityLogApiController::class, 'show'])->name('api.activitylog.show');
        Route::delete('/{id}', [ActivityLogApiController::class, 'destroy'])->name('api.activitylog.destroy');
        Route::post('/bulk-delete', [ActivityLogApiController::class, 'bulkDestroy'])->name('api.activitylog.bulk-destroy');
        Route::delete('/date-group/{date}', [ActivityLogApiController::class, 'destroyDateGroup'])->name('api.activitylog.destroy-date-group');
    });
