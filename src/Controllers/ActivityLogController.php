<?php

namespace Rahad\ActivityLog\Controllers;

use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\View\View;
use Rahad\ActivityLog\Models\ActivityLog;

class ActivityLogController extends Controller
{
    /**
     * Display a listing of the activity logs with filters, date groups, and search.
     */
    public function index(Request $request): View
    {
        $modelClass = config('activitylog.model', ActivityLog::class);

        $search = $request->string('search')->trim()->toString();
        $roleFilter = $request->string('role')->trim()->toString();
        $moduleFilter = $request->string('module')->trim()->toString();
        $actionFilter = $request->string('action')->trim()->toString();
        $selectedDate = $request->string('selected_date')->trim()->toString() ?: null;
        $dateFrom = $request->string('date_from')->trim()->toString();
        $dateTo = $request->string('date_to')->trim()->toString();

        // Default to today if no filters or search are specified
        if ($selectedDate === null && ! $request->has('all') && $dateFrom === '' && $dateTo === '' && $search === '' && $roleFilter === '' && $moduleFilter === '' && $actionFilter === '') {
            $selectedDate = Carbon::today()->toDateString();
        }

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

        if ($roleFilter !== '') {
            $query->where('role', $roleFilter);
        }

        if ($moduleFilter !== '') {
            $query->where('module', $moduleFilter);
        }

        if ($actionFilter !== '') {
            $query->where('action', $actionFilter);
        }

        if ($selectedDate !== null) {
            $query->whereDate('created_at', $selectedDate);
        } elseif ($dateFrom !== '' && $dateTo !== '') {
            $query->whereBetween('created_at', [$dateFrom, $dateTo . ' 23:59:59']);
        } elseif ($dateFrom !== '') {
            $query->where('created_at', '>=', $dateFrom);
        } elseif ($dateTo !== '') {
            $query->where('created_at', '<=', $dateTo . ' 23:59:59');
        }

        $perPage = (int) config('activitylog.web.per_page', 20);

        $logs = $query->with(['causer', 'subject'])
            ->orderByDesc('created_at')
            ->paginate($perPage)
            ->withQueryString();

        // Retrieve date groups for timeline sidebar
        $dateGroups = $modelClass::selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderByDesc('date')
            ->limit(30)
            ->get()
            ->map(fn ($item) => [
                'date' => (string) $item->date,
                'label' => $this->formatDateLabel((string) $item->date),
                'count' => (int) $item->count,
            ])
            ->toArray();

        // Retrieve distinct filter dropdown options
        $uniqueModules = $modelClass::distinct()->pluck('module')->filter()->sort()->values()->toArray();
        $uniqueRoles = $modelClass::distinct()->pluck('role')->filter()->sort()->values()->toArray();
        $uniqueActions = $modelClass::distinct()->pluck('action')->filter()->sort()->values()->toArray();

        return view('activitylog::index', [
            'logs' => $logs,
            'dateGroups' => $dateGroups,
            'uniqueModules' => $uniqueModules,
            'uniqueRoles' => $uniqueRoles,
            'uniqueActions' => $uniqueActions,
            'search' => $search,
            'roleFilter' => $roleFilter,
            'moduleFilter' => $moduleFilter,
            'actionFilter' => $actionFilter,
            'selectedDate' => $selectedDate,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'layout' => config('activitylog.web.layout', 'activitylog::layouts.blank'),
            'homeUrl' => config('activitylog.web.home_url', '/'),
            'title' => config('activitylog.web.title', 'Activity Log'),
        ]);
    }

    /**
     * Display the specified activity log detail.
     */
    public function show(int|string $id): View|RedirectResponse
    {
        $modelClass = config('activitylog.model', ActivityLog::class);

        $log = $modelClass::with(['causer', 'subject'])->find($id);

        if (! $log) {
            return redirect()
                ->route('activitylog.index')
                ->with('error', 'Activity log record not found.');
        }

        return view('activitylog::view', [
            'log' => $log,
            'layout' => config('activitylog.web.layout', 'activitylog::layouts.blank'),
            'homeUrl' => config('activitylog.web.home_url', '/'),
            'title' => config('activitylog.web.title', 'Activity Log Details'),
        ]);
    }

    /**
     * Delete a single activity log record.
     */
    public function destroy(int|string $id): RedirectResponse
    {
        $modelClass = config('activitylog.model', ActivityLog::class);

        $modelClass::where('id', $id)->delete();

        return redirect()
            ->back()
            ->with('status', 'Activity log deleted successfully.');
    }

    /**
     * Bulk delete activity logs by selected IDs.
     */
    public function bulkDestroy(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['required', 'integer'],
        ]);

        $modelClass = config('activitylog.model', ActivityLog::class);

        $count = $modelClass::whereIn('id', $validated['ids'])->delete();

        return redirect()
            ->back()
            ->with('status', "{$count} activity log(s) deleted successfully.");
    }

    /**
     * Delete all activity logs for a specific date (YYYY-MM-DD).
     */
    public function destroyDateGroup(Request $request, string $date): RedirectResponse
    {
        $modelClass = config('activitylog.model', ActivityLog::class);

        $count = $modelClass::whereDate('created_at', $date)->delete();

        return redirect()
            ->route('activitylog.index')
            ->with('status', "{$count} activity log(s) deleted for {$date}.");
    }

    /**
     * Format date string into human-friendly label.
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
