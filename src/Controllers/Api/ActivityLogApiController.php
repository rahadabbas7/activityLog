<?php

namespace Rahad\ActivityLog\Controllers\Api;

use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Rahad\ActivityLog\Models\ActivityLog;

class ActivityLogApiController extends Controller
{
    /**
     * Display a paginated listing of activity logs with search & filters.
     */
    public function index(Request $request): JsonResponse
    {
        $modelClass = config('activitylog.model', ActivityLog::class);

        $search = $request->string('search')->trim()->toString();
        $role = $request->string('role')->trim()->toString();
        $module = $request->string('module')->trim()->toString();
        $action = $request->string('action')->trim()->toString();
        $selectedDate = $request->string('selected_date')->trim()->toString();
        $dateFrom = $request->string('date_from')->trim()->toString();
        $dateTo = $request->string('date_to')->trim()->toString();

        $query = $modelClass::query();

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('module', 'like', "%{$search}%")
                    ->orWhere('action', 'like', "%{$search}%")
                    ->orWhere('role', 'like', "%{$search}%")
                    ->orWhereHas('causer', function ($q2) use ($search) {
                        $q2->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        if ($role !== '') {
            $query->where('role', $role);
        }

        if ($module !== '') {
            $query->where('module', $module);
        }

        if ($action !== '') {
            $query->where('action', $action);
        }

        if ($selectedDate !== '') {
            $query->whereDate('created_at', $selectedDate);
        } elseif ($dateFrom !== '' && $dateTo !== '') {
            $query->whereBetween('created_at', [$dateFrom, $dateTo . ' 23:59:59']);
        } elseif ($dateFrom !== '') {
            $query->where('created_at', '>=', $dateFrom);
        } elseif ($dateTo !== '') {
            $query->where('created_at', '<=', $dateTo . ' 23:59:59');
        }

        $perPage = (int) ($request->input('per_page') ?: config('activitylog.api.per_page', 20));

        $paginated = $query->with(['causer', 'subject'])
            ->orderByDesc('created_at')
            ->paginate($perPage)
            ->through(fn ($log) => [
                'id' => $log->id,
                'log_name' => $log->log_name,
                'title' => $log->title,
                'module' => $log->module,
                'action' => $log->action,
                'role' => $log->role,
                'causer' => $log->causer ? [
                    'id' => $log->causer->getKey(),
                    'type' => class_basename($log->causer_type),
                    'name' => $log->causer_name,
                ] : null,
                'subject' => $log->subject ? [
                    'id' => $log->subject->getKey(),
                    'type' => class_basename($log->subject_type),
                    'name' => $log->subject_name,
                ] : null,
                'created_at' => $log->created_at?->toISOString(),
                'created_at_diff' => $log->created_at?->diffForHumans(),
                'created_at_formatted' => $log->created_at?->format('M d, Y h:i A'),
            ]);

        return response()->json([
            'success' => true,
            'message' => 'Activity logs retrieved successfully.',
            'data' => $paginated->items(),
            'meta' => [
                'current_page' => $paginated->currentPage(),
                'last_page' => $paginated->lastPage(),
                'per_page' => $paginated->perPage(),
                'total' => $paginated->total(),
            ],
        ]);
    }

    /**
     * Retrieve aggregated date groups with counts for timeline.
     */
    public function dateGroups(): JsonResponse
    {
        $modelClass = config('activitylog.model', ActivityLog::class);

        $groups = $modelClass::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderByDesc('date')
            ->limit(30)
            ->get()
            ->map(fn ($item) => [
                'date' => (string) $item->date,
                'label' => $this->formatDateLabel((string) $item->date),
                'count' => (int) $item->count,
            ])
            ->values();

        return response()->json([
            'success' => true,
            'message' => 'Activity date groups retrieved successfully.',
            'data' => $groups,
        ]);
    }

    /**
     * Retrieve available distinct filter options (modules, roles, actions).
     */
    public function filterOptions(): JsonResponse
    {
        $modelClass = config('activitylog.model', ActivityLog::class);

        $modules = $modelClass::distinct()->pluck('module')->filter()->sort()->values()->toArray();
        $roles = $modelClass::distinct()->pluck('role')->filter()->sort()->values()->toArray();
        $actions = $modelClass::distinct()->pluck('action')->filter()->sort()->values()->toArray();

        return response()->json([
            'success' => true,
            'message' => 'Filter options retrieved successfully.',
            'data' => [
                'modules' => $modules,
                'roles' => $roles,
                'actions' => $actions,
            ],
        ]);
    }

    /**
     * Display the specified activity log detail.
     */
    public function show(int|string $id): JsonResponse
    {
        $modelClass = config('activitylog.model', ActivityLog::class);

        $log = $modelClass::with(['causer', 'subject'])->find($id);

        if (! $log) {
            return response()->json([
                'success' => false,
                'message' => 'Activity log not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Activity log details retrieved successfully.',
            'data' => [
                'id' => $log->id,
                'log_name' => $log->log_name,
                'title' => $log->title,
                'module' => $log->module,
                'action' => $log->action,
                'role' => $log->role,
                'causer' => $log->causer ? [
                    'id' => $log->causer->getKey(),
                    'type' => $log->causer_type,
                    'name' => $log->causer_name,
                ] : null,
                'subject' => $log->subject ? [
                    'id' => $log->subject->getKey(),
                    'type' => $log->subject_type,
                    'name' => $log->subject_name,
                ] : null,
                'old_values' => $log->old_values,
                'new_values' => $log->new_values,
                'metadata' => $log->metadata,
                'request' => [
                    'ip_address' => $log->ip_address,
                    'method' => $log->method,
                    'url' => $log->url,
                    'user_agent' => $log->user_agent,
                ],
                'created_at' => $log->created_at?->toISOString(),
                'created_at_diff' => $log->created_at?->diffForHumans(),
                'created_at_formatted' => $log->created_at?->format('M d, Y h:i:s A'),
            ],
        ]);
    }

    /**
     * Delete a single activity log.
     */
    public function destroy(int|string $id): JsonResponse
    {
        $modelClass = config('activitylog.model', ActivityLog::class);

        $log = $modelClass::find($id);

        if (! $log) {
            return response()->json([
                'success' => false,
                'message' => 'Activity log not found.',
            ], 404);
        }

        $log->delete();

        return response()->json([
            'success' => true,
            'message' => 'Activity log deleted successfully.',
        ]);
    }

    /**
     * Bulk delete activity logs by array of IDs.
     */
    public function bulkDestroy(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['required', 'integer'],
        ]);

        $modelClass = config('activitylog.model', ActivityLog::class);

        $count = $modelClass::whereIn('id', $validated['ids'])->delete();

        return response()->json([
            'success' => true,
            'message' => "{$count} activity log(s) deleted successfully.",
            'data' => [
                'deleted_count' => $count,
            ],
        ]);
    }

    /**
     * Delete all activity logs for a specific date (YYYY-MM-DD).
     */
    public function destroyDateGroup(string $date): JsonResponse
    {
        $modelClass = config('activitylog.model', ActivityLog::class);

        $count = $modelClass::whereDate('created_at', $date)->delete();

        return response()->json([
            'success' => true,
            'message' => "{$count} activity log(s) deleted for {$date}.",
            'data' => [
                'date' => $date,
                'deleted_count' => $count,
            ],
        ]);
    }

    /**
     * Format a date string into a user-friendly label.
     */
    private function formatDateLabel(string $date): string
    {
        $d = Carbon::parse($date);
        $today = Carbon::today();
        $yesterday = Carbon::yesterday();

        if ($d->isSameDay($today)) {
            return 'Today';
        }
        if ($d->isSameDay($yesterday)) {
            return 'Yesterday';
        }

        return $d->format('M d');
    }
}
