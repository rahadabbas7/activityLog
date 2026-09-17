@extends($layout ?? 'activitylog::layouts.blank')

@section('content')
<div class="min-h-screen bg-slate-50 dark:bg-slate-950">
    {{-- Header --}}
    <header class="sticky top-0 z-30 bg-white/80 dark:bg-slate-900/80 backdrop-blur-xl border-b border-slate-200/60 dark:border-slate-800">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 py-3 flex items-center gap-3">
            <a
                href="{{ route('activitylog.index') }}"
                class="inline-flex items-center gap-2 px-3 py-2 rounded-xl text-sm font-medium text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-slate-200 transition-colors"
            >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Back
            </a>
            <div class="flex-1 min-w-0">
                <h1 class="text-lg font-bold text-slate-900 dark:text-slate-100 truncate">{{ $log->title }}</h1>
                <p class="text-xs text-slate-400">{{ $log->created_at?->diffForHumans() }} &middot; {{ $log->created_at?->format('M d, Y h:i:s A') }}</p>
            </div>
            <form action="{{ route('activitylog.destroy', $log->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to permanently delete this activity log?');">
                @csrf
                @method('DELETE')
                <button
                    type="submit"
                    class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-sm font-medium text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/40 border border-rose-200 dark:border-rose-900 transition-colors"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    Delete
                </button>
            </form>
        </div>
    </header>

    {{-- Content --}}
    <div class="max-w-4xl mx-auto px-4 sm:px-6 py-6 space-y-6">
        {{-- Quick Info Cards --}}
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
            @php
                $actionColor = match($log->action) {
                    'created' => ['bg' => 'bg-emerald-50 dark:bg-emerald-950/50', 'text' => 'text-emerald-600 dark:text-emerald-400', 'border' => 'border-emerald-100 dark:border-emerald-900', 'dot' => 'bg-emerald-400'],
                    'updated' => ['bg' => 'bg-amber-50 dark:bg-amber-950/50', 'text' => 'text-amber-600 dark:text-amber-400', 'border' => 'border-amber-100 dark:border-amber-900', 'dot' => 'bg-amber-400'],
                    'deleted' => ['bg' => 'bg-rose-50 dark:bg-rose-950/50', 'text' => 'text-rose-600 dark:text-rose-400', 'border' => 'border-rose-100 dark:border-rose-900', 'dot' => 'bg-rose-400'],
                    'login' => ['bg' => 'bg-sky-50 dark:bg-sky-950/50', 'text' => 'text-sky-600 dark:text-sky-400', 'border' => 'border-sky-100 dark:border-sky-900', 'dot' => 'bg-sky-400'],
                    'logout' => ['bg' => 'bg-slate-50 dark:bg-slate-800', 'text' => 'text-slate-600 dark:text-slate-400', 'border' => 'border-slate-100 dark:border-slate-700', 'dot' => 'bg-slate-400'],
                    default => ['bg' => 'bg-violet-50 dark:bg-violet-950/50', 'text' => 'text-violet-600 dark:text-violet-400', 'border' => 'border-violet-100 dark:border-violet-900', 'dot' => 'bg-violet-400'],
                };
            @endphp
            <div class="p-3 rounded-xl bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 shadow-sm">
                <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400 mb-1">Action</p>
                <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-lg text-xs font-semibold {{ $actionColor['bg'] }} {{ $actionColor['text'] }} border {{ $actionColor['border'] }}">
                    <span class="w-1.5 h-1.5 rounded-full {{ $actionColor['dot'] }}"></span>
                    {{ ucfirst($log->action) }}
                </span>
            </div>
            <div class="p-3 rounded-xl bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 shadow-sm">
                <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400 mb-1">Module</p>
                <p class="text-sm font-bold text-slate-800 dark:text-slate-200 truncate">{{ $log->module }}</p>
            </div>
            <div class="p-3 rounded-xl bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 shadow-sm">
                <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400 mb-1">User</p>
                <p class="text-sm font-bold text-slate-800 dark:text-slate-200 truncate">{{ $log->causer_name }}</p>
            </div>
            <div class="p-3 rounded-xl bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 shadow-sm">
                <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400 mb-1">Role</p>
                <p class="text-sm font-bold text-slate-800 dark:text-slate-200 truncate">{{ $log->role ?? '—' }}</p>
            </div>
            <div class="p-3 rounded-xl bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 shadow-sm">
                <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400 mb-1">Subject</p>
                <p class="text-sm font-bold text-slate-800 dark:text-slate-200 truncate">{{ $log->subject_name }}</p>
            </div>
            <div class="p-3 rounded-xl bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 shadow-sm">
                <p class="text-[10px] font-semibold uppercase tracking-wider text-slate-400 mb-1">IP Address</p>
                <p class="text-sm font-bold text-slate-800 dark:text-slate-200 font-mono truncate">{{ $log->ip_address ?? '—' }}</p>
            </div>
        </div>

        {{-- Request Information --}}
        @if ($log->method || $log->url || $log->user_agent)
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/60 dark:border-slate-800 shadow-sm overflow-hidden">
                <div class="px-5 py-3 border-b border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/50">
                    <h3 class="text-xs font-semibold uppercase tracking-wider text-slate-500">Request Information</h3>
                </div>
                <div class="p-5 space-y-3">
                    @if ($log->method)
                        <div class="flex items-start gap-3">
                            <span class="text-xs font-semibold text-slate-500 w-20 shrink-0 pt-0.5">Method</span>
                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-bold font-mono
                                {{ $log->method === 'GET' ? 'bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 border border-emerald-100 dark:border-emerald-900' : ($log->method === 'POST' ? 'bg-amber-50 dark:bg-amber-950/50 text-amber-600 border border-amber-100 dark:border-amber-900' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 border border-slate-200 dark:border-slate-700') }}">
                                {{ $log->method }}
                            </span>
                        </div>
                    @endif
                    @if ($log->url)
                        <div class="flex items-start gap-3">
                            <span class="text-xs font-semibold text-slate-500 w-20 shrink-0 pt-0.5">URL</span>
                            <span class="text-sm font-mono text-slate-700 dark:text-slate-300 break-all">{{ $log->url }}</span>
                        </div>
                    @endif
                    @if ($log->user_agent)
                        <div class="flex items-start gap-3">
                            <span class="text-xs font-semibold text-slate-500 w-20 shrink-0 pt-0.5">Agent</span>
                            <span class="text-xs font-mono text-slate-500 dark:text-slate-400 break-all leading-relaxed">{{ $log->user_agent }}</span>
                        </div>
                    @endif
                </div>
            </div>
        @endif

        {{-- Changes Diff --}}
        @if (!empty($log->old_values) || !empty($log->new_values))
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/60 dark:border-slate-800 shadow-sm overflow-hidden">
                <div class="px-5 py-3 border-b border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/50">
                    <h3 class="text-xs font-semibold uppercase tracking-wider text-slate-500">Changes Diff</h3>
                </div>
                <div class="divide-y divide-slate-100 dark:divide-slate-800">
                    @php
                        $allKeys = array_unique(array_merge(
                            array_keys($log->old_values ?? []),
                            array_keys($log->new_values ?? [])
                        ));
                    @endphp
                    @foreach ($allKeys as $key)
                        <div class="px-5 py-3 flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-4">
                            <span class="text-xs font-mono font-semibold text-slate-500 min-w-[120px] shrink-0">{{ $key }}</span>
                            <div class="flex items-center gap-2 flex-wrap min-w-0">
                                @if (isset($log->old_values[$key]))
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md bg-rose-50 dark:bg-rose-950/50 text-rose-600 dark:text-rose-400 text-xs font-mono border border-rose-100 dark:border-rose-900 break-all">
                                        {{ is_array($log->old_values[$key]) ? json_encode($log->old_values[$key]) : (string) $log->old_values[$key] }}
                                    </span>
                                @else
                                    <span class="text-xs text-slate-300 dark:text-slate-600 font-mono">null</span>
                                @endif

                                @if (isset($log->new_values[$key]))
                                    <svg class="w-4 h-4 text-slate-300 dark:text-slate-600 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md bg-emerald-50 dark:bg-emerald-950/50 text-emerald-600 dark:text-emerald-400 text-xs font-mono border border-emerald-100 dark:border-emerald-900 break-all">
                                        {{ is_array($log->new_values[$key]) ? json_encode($log->new_values[$key]) : (string) $log->new_values[$key] }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Metadata JSON --}}
        @if (!empty($log->metadata))
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/60 dark:border-slate-800 shadow-sm overflow-hidden">
                <div class="px-5 py-3 border-b border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/50">
                    <h3 class="text-xs font-semibold uppercase tracking-wider text-slate-500">Metadata</h3>
                </div>
                <div class="p-5">
                    <pre class="text-xs font-mono text-slate-600 dark:text-slate-300 whitespace-pre-wrap break-all bg-slate-50 dark:bg-slate-800/70 rounded-xl p-4 border border-slate-100 dark:border-slate-800">{{ json_encode($log->metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection
