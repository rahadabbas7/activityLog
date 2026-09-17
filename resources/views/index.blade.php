@extends($layout ?? 'activitylog::layouts.blank')

@section('content')
<div class="flex h-screen overflow-hidden bg-slate-50 dark:bg-slate-950" x-data="{
    sidebarOpen: true,
    mobileSidebar: false,
    selected: [],
    selectAll: false,
    toggleAll() {
        if (this.selectAll) {
            this.selected = Array.from(document.querySelectorAll('.log-checkbox')).map(el => el.value);
        } else {
            this.selected = [];
        }
    },
    toggleRow(id) {
        id = id.toString();
        if (this.selected.includes(id)) {
            this.selected = this.selected.filter(item => item !== id);
        } else {
            this.selected.push(id);
        }
        const allCheckboxes = document.querySelectorAll('.log-checkbox');
        this.selectAll = allCheckboxes.length > 0 && this.selected.length === allCheckboxes.length;
    }
}">
    {{-- Desktop Sidebar --}}
    <aside
        class="hidden lg:flex flex-col w-64 shrink-0 bg-white dark:bg-slate-900 border-r border-slate-200/80 dark:border-slate-800 transition-all duration-300"
        :class="{ 'w-64': sidebarOpen, 'w-0 overflow-hidden': !sidebarOpen }"
    >
        <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100 dark:border-slate-800">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-violet-500 to-indigo-600 flex items-center justify-center shadow-sm shadow-violet-500/25">
                    <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <span class="font-semibold text-sm text-slate-800 dark:text-slate-200">Activity Timeline</span>
            </div>
            <button @click="sidebarOpen = !sidebarOpen" class="p-1 rounded-md hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 transition-colors">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"/></svg>
            </button>
        </div>
        <nav class="flex-1 overflow-y-auto scrollbar-thin py-3 px-3 space-y-0.5">
            <a
                href="{{ route('activitylog.index', array_merge(request()->except(['selected_date', 'page']), ['all' => 1])) }}"
                class="w-full flex items-center justify-between px-3 py-2.5 rounded-xl text-sm transition-all duration-200
                    {{ $selectedDate === null ? 'bg-gradient-to-r from-violet-500 to-indigo-600 text-white shadow-md shadow-violet-500/20' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800/50 hover:text-slate-900 dark:hover:text-slate-200' }}"
            >
                <span class="flex items-center gap-2.5">
                    <span class="w-1.5 h-1.5 rounded-full {{ $selectedDate === null ? 'bg-white' : 'bg-slate-300 dark:bg-slate-600' }}"></span>
                    All Activity
                </span>
            </a>
            @foreach ($dateGroups as $group)
                <div class="flex items-center group">
                    <a
                        href="{{ route('activitylog.index', array_merge(request()->except(['selected_date', 'all', 'page']), ['selected_date' => $group['date']])) }}"
                        class="flex-1 flex items-center justify-between px-3 py-2.5 rounded-xl text-sm transition-all duration-200
                            {{ $selectedDate === $group['date']
                                ? 'bg-gradient-to-r from-violet-500 to-indigo-600 text-white shadow-md shadow-violet-500/20'
                                : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800/50 hover:text-slate-900 dark:hover:text-slate-200' }}"
                    >
                        <span class="flex items-center gap-2.5">
                            <span class="w-1.5 h-1.5 rounded-full {{ $selectedDate === $group['date'] ? 'bg-white' : 'bg-slate-300 dark:bg-slate-600' }}"></span>
                            {{ $group['label'] }}
                        </span>
                        <span class="text-xs font-medium px-2 py-0.5 rounded-full {{ $selectedDate === $group['date'] ? 'bg-white/20 text-white' : 'bg-slate-100 dark:bg-slate-800 text-slate-500 dark:text-slate-400' }}">
                            {{ $group['count'] }}
                        </span>
                    </a>
                    <form
                        action="{{ route('activitylog.destroy-date-group', $group['date']) }}"
                        method="POST"
                        onsubmit="return confirm('Permanently delete all {{ $group['count'] }} activity log(s) for {{ $group['label'] }} ({{ $group['date'] }})?');"
                        class="shrink-0"
                    >
                        @csrf
                        @method('DELETE')
                        <button
                            type="submit"
                            class="p-1.5 rounded-lg text-slate-300 dark:text-slate-600 hover:text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-950/40 transition-all opacity-0 group-hover:opacity-100"
                            title="Delete all logs for {{ $group['label'] }}"
                        >
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </form>
                </div>
            @endforeach
            @if ($selectedDate)
                <a
                    href="{{ route('activitylog.index', array_merge(request()->except(['selected_date', 'page']), ['all' => 1])) }}"
                    class="w-full flex items-center gap-2 px-3 py-2.5 rounded-xl text-sm text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-950/40 transition-colors mt-2"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    Clear date filter
                </a>
            @endif
        </nav>
    </aside>

    {{-- Mobile Sidebar Panel --}}
    <div
        x-show="mobileSidebar"
        x-transition:enter="transition-opacity ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition-opacity ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @click="mobileSidebar = false"
        class="fixed inset-0 z-40 bg-black/40 backdrop-blur-sm lg:hidden"
    ></div>

    <aside
        x-show="mobileSidebar"
        x-transition:enter="transition-transform ease-out duration-300"
        x-transition:enter-start="-translate-x-full"
        x-transition:enter-end="translate-x-0"
        x-transition:leave="transition-transform ease-in duration-200"
        x-transition:leave-start="translate-x-0"
        x-transition:leave-end="-translate-x-full"
        class="fixed inset-y-0 left-0 z-50 w-72 bg-white dark:bg-slate-900 shadow-2xl lg:hidden flex flex-col"
    >
        <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100 dark:border-slate-800">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-violet-500 to-indigo-600 flex items-center justify-center shadow-sm shadow-violet-500/25">
                    <svg class="w-4 h-4 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <span class="font-semibold text-sm text-slate-800 dark:text-slate-200">Activity Timeline</span>
            </div>
            <button @click="mobileSidebar = false" class="p-1 rounded-md hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-400 hover:text-slate-600">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <nav class="flex-1 overflow-y-auto scrollbar-thin py-3 px-3 space-y-0.5">
            <a
                href="{{ route('activitylog.index', array_merge(request()->except(['selected_date', 'page']), ['all' => 1])) }}"
                class="w-full flex items-center justify-between px-3 py-2.5 rounded-xl text-sm transition-all duration-200
                    {{ $selectedDate === null ? 'bg-gradient-to-r from-violet-500 to-indigo-600 text-white shadow-md shadow-violet-500/20' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 hover:text-slate-900' }}"
            >
                <span class="flex items-center gap-2.5">
                    <span class="w-1.5 h-1.5 rounded-full {{ $selectedDate === null ? 'bg-white' : 'bg-slate-300' }}"></span>
                    All Activity
                </span>
            </a>
            @foreach ($dateGroups as $group)
                <div class="flex items-center">
                    <a
                        href="{{ route('activitylog.index', array_merge(request()->except(['selected_date', 'all', 'page']), ['selected_date' => $group['date']])) }}"
                        class="flex-1 flex items-center justify-between px-3 py-2.5 rounded-xl text-sm transition-all duration-200
                            {{ $selectedDate === $group['date']
                                ? 'bg-gradient-to-r from-violet-500 to-indigo-600 text-white shadow-md shadow-violet-500/20'
                                : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 hover:text-slate-900' }}"
                    >
                        <span class="flex items-center gap-2.5">
                            <span class="w-1.5 h-1.5 rounded-full {{ $selectedDate === $group['date'] ? 'bg-white' : 'bg-slate-300' }}"></span>
                            {{ $group['label'] }}
                        </span>
                        <span class="text-xs font-medium px-2 py-0.5 rounded-full {{ $selectedDate === $group['date'] ? 'bg-white/20 text-white' : 'bg-slate-100 dark:bg-slate-800 text-slate-500' }}">
                            {{ $group['count'] }}
                        </span>
                    </a>
                </div>
            @endforeach
        </nav>
    </aside>

    {{-- Main Content --}}
    <main class="flex-1 flex flex-col min-w-0 overflow-hidden">
        {{-- Header Bar --}}
        <header class="shrink-0 bg-white/80 dark:bg-slate-900/80 backdrop-blur-xl border-b border-slate-200/60 dark:border-slate-800 px-4 sm:px-6 py-3 flex items-center gap-3">
            <button @click="mobileSidebar = !mobileSidebar" class="lg:hidden p-2 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-500 transition-colors">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
            <button @click="sidebarOpen = !sidebarOpen" class="hidden lg:flex p-2 rounded-xl hover:bg-slate-100 dark:hover:bg-slate-800 text-slate-500 transition-colors">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>
            <div class="flex-1 min-w-0">
                <h1 class="text-lg font-bold text-slate-900 dark:text-slate-100">{{ $title }}</h1>
                <p class="text-xs text-slate-400 hidden sm:block">Track and audit system activities and data changes</p>
            </div>
            <div class="flex items-center gap-2">
                @if (session('status'))
                    <span class="text-xs font-medium text-emerald-600 bg-emerald-50 dark:bg-emerald-950/50 px-2.5 py-1 rounded-full hidden sm:inline border border-emerald-100 dark:border-emerald-900">
                        {{ session('status') }}
                    </span>
                @endif
                @if (session('error'))
                    <span class="text-xs font-medium text-rose-600 bg-rose-50 dark:bg-rose-950/50 px-2.5 py-1 rounded-full hidden sm:inline border border-rose-100 dark:border-rose-900">
                        {{ session('error') }}
                    </span>
                @endif
                @if ($logs->total() > 0)
                    <span class="text-xs font-medium text-slate-500 bg-slate-100 dark:bg-slate-800 px-2.5 py-1 rounded-full hidden sm:inline">
                        {{ number_format($logs->total()) }} total
                    </span>
                @endif
                @if ($homeUrl)
                    <a
                        href="{{ $homeUrl }}"
                        class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-semibold text-white bg-gradient-to-r from-violet-500 to-indigo-600 hover:from-violet-600 hover:to-indigo-700 shadow-sm shadow-violet-500/25 transition-all hover:-translate-y-0.5"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                        Dashboard
                    </a>
                @endif
            </div>
        </header>

        {{-- Filters Bar --}}
        <div class="shrink-0 bg-white dark:bg-slate-900 border-b border-slate-100 dark:border-slate-800 px-4 sm:px-6 py-4">
            <form action="{{ route('activitylog.index') }}" method="GET" class="flex flex-col sm:flex-row gap-3">
                @if ($selectedDate)
                    <input type="hidden" name="selected_date" value="{{ $selectedDate }}">
                @endif
                <div class="relative flex-1 max-w-md">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input
                        type="text"
                        name="search"
                        value="{{ $search }}"
                        placeholder="Search activities..."
                        class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800/50 text-sm text-slate-700 dark:text-slate-200 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-violet-500/20 focus:border-violet-400 transition-all"
                    >
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <select name="role" onchange="this.form.submit()" class="px-3 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800/50 text-sm text-slate-600 dark:text-slate-300 focus:outline-none focus:ring-2 focus:ring-violet-500/20 focus:border-violet-400 transition-all">
                        <option value="">All Roles</option>
                        @foreach ($uniqueRoles as $role)
                            <option value="{{ $role }}" {{ $roleFilter === $role ? 'selected' : '' }}>{{ ucfirst($role) }}</option>
                        @endforeach
                    </select>

                    <select name="module" onchange="this.form.submit()" class="px-3 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800/50 text-sm text-slate-600 dark:text-slate-300 focus:outline-none focus:ring-2 focus:ring-violet-500/20 focus:border-violet-400 transition-all">
                        <option value="">All Modules</option>
                        @foreach ($uniqueModules as $module)
                            <option value="{{ $module }}" {{ $moduleFilter === $module ? 'selected' : '' }}>{{ ucfirst($module) }}</option>
                        @endforeach
                    </select>

                    <select name="action" onchange="this.form.submit()" class="px-3 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800/50 text-sm text-slate-600 dark:text-slate-300 focus:outline-none focus:ring-2 focus:ring-violet-500/20 focus:border-violet-400 transition-all">
                        <option value="">All Actions</option>
                        @foreach ($uniqueActions as $act)
                            <option value="{{ $act }}" {{ $actionFilter === $act ? 'selected' : '' }}>{{ ucfirst($act) }}</option>
                        @endforeach
                    </select>

                    <input type="date" name="date_from" value="{{ $dateFrom }}" onchange="this.form.submit()" class="px-3 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800/50 text-sm text-slate-600 dark:text-slate-300 focus:outline-none focus:ring-2 focus:ring-violet-500/20 focus:border-violet-400 transition-all" title="From date">
                    <input type="date" name="date_to" value="{{ $dateTo }}" onchange="this.form.submit()" class="px-3 py-2.5 rounded-xl border border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800/50 text-sm text-slate-600 dark:text-slate-300 focus:outline-none focus:ring-2 focus:ring-violet-500/20 focus:border-violet-400 transition-all" title="To date">

                    <button type="submit" class="px-4 py-2.5 rounded-xl text-sm font-medium bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors">
                        Filter
                    </button>

                    @if ($search || $roleFilter || $moduleFilter || $actionFilter || $dateFrom || $dateTo || $selectedDate)
                        <a href="{{ route('activitylog.index') }}" class="flex items-center gap-1.5 px-3 py-2.5 rounded-xl text-sm text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/40 border border-rose-200 dark:border-rose-900 transition-colors">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                            Clear
                        </a>
                    @endif
                </div>
            </form>
        </div>

        {{-- Bulk Action Bar --}}
        <div
            x-show="selected.length > 0"
            x-cloak
            class="shrink-0 bg-violet-50 dark:bg-violet-950/40 border-b border-violet-100 dark:border-violet-900 px-4 sm:px-6 py-3 flex items-center justify-between"
        >
            <span class="text-sm font-semibold text-violet-700 dark:text-violet-300">
                <span x-text="selected.length"></span> item(s) selected
            </span>
            <form action="{{ route('activitylog.bulk-destroy') }}" method="POST" onsubmit="return confirm('Are you sure you want to permanently delete the selected activity logs?');">
                @csrf
                <template x-for="id in selected" :key="id">
                    <input type="hidden" name="ids[]" :value="id">
                </template>
                <button
                    type="submit"
                    class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-sm font-semibold text-white bg-rose-500 hover:bg-rose-600 shadow-sm transition-colors"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    Delete Selected
                </button>
            </form>
        </div>

        {{-- Table Container --}}
        <div class="flex-1 overflow-auto p-4 sm:p-6">
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200/60 dark:border-slate-800 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-slate-100 dark:border-slate-800 bg-slate-50/80 dark:bg-slate-800/50">
                                <th class="text-center px-3 py-3.5 w-10">
                                    <input
                                        type="checkbox"
                                        x-model="selectAll"
                                        @change="toggleAll()"
                                        class="w-4 h-4 rounded border-slate-300 text-violet-600 focus:ring-violet-500 cursor-pointer"
                                    >
                                </th>
                                <th class="text-left px-5 py-3.5 font-semibold text-xs uppercase tracking-wider text-slate-500">Time</th>
                                <th class="text-left px-5 py-3.5 font-semibold text-xs uppercase tracking-wider text-slate-500">Title</th>
                                <th class="text-left px-5 py-3.5 font-semibold text-xs uppercase tracking-wider text-slate-500 hidden md:table-cell">Module</th>
                                <th class="text-left px-5 py-3.5 font-semibold text-xs uppercase tracking-wider text-slate-500 hidden lg:table-cell">Action</th>
                                <th class="text-left px-5 py-3.5 font-semibold text-xs uppercase tracking-wider text-slate-500 hidden sm:table-cell">User</th>
                                <th class="text-left px-5 py-3.5 font-semibold text-xs uppercase tracking-wider text-slate-500 hidden lg:table-cell">Role</th>
                                <th class="text-left px-5 py-3.5 font-semibold text-xs uppercase tracking-wider text-slate-500 hidden xl:table-cell">Subject</th>
                                <th class="text-center px-5 py-3.5 font-semibold text-xs uppercase tracking-wider text-slate-500 w-24">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            @forelse ($logs as $log)
                                @php
                                    $actionColor = match($log->action) {
                                        'created' => ['bg' => 'bg-emerald-50 dark:bg-emerald-950/50', 'text' => 'text-emerald-600 dark:text-emerald-400', 'border' => 'border-emerald-100 dark:border-emerald-900'],
                                        'updated' => ['bg' => 'bg-amber-50 dark:bg-amber-950/50', 'text' => 'text-amber-600 dark:text-amber-400', 'border' => 'border-amber-100 dark:border-amber-900'],
                                        'deleted' => ['bg' => 'bg-rose-50 dark:bg-rose-950/50', 'text' => 'text-rose-600 dark:text-rose-400', 'border' => 'border-rose-100 dark:border-rose-900'],
                                        'login' => ['bg' => 'bg-sky-50 dark:bg-sky-950/50', 'text' => 'text-sky-600 dark:text-sky-400', 'border' => 'border-sky-100 dark:border-sky-900'],
                                        'logout' => ['bg' => 'bg-slate-50 dark:bg-slate-800', 'text' => 'text-slate-600 dark:text-slate-400', 'border' => 'border-slate-100 dark:border-slate-700'],
                                        default => ['bg' => 'bg-violet-50 dark:bg-violet-950/50', 'text' => 'text-violet-600 dark:text-violet-400', 'border' => 'border-violet-100 dark:border-violet-900'],
                                    };
                                    $timeAgo = $log->created_at?->diffForHumans();
                                    $exactTime = $log->created_at?->format('M d, Y h:i A');
                                @endphp
                                <tr
                                    class="transition-colors hover:bg-slate-50/50 dark:hover:bg-slate-800/40"
                                    :class="{ 'bg-violet-50/50 dark:bg-violet-950/30': selected.includes('{{ $log->id }}') }"
                                >
                                    <td class="px-3 py-3.5 text-center">
                                        <input
                                            type="checkbox"
                                            value="{{ $log->id }}"
                                            class="log-checkbox w-4 h-4 rounded border-slate-300 text-violet-600 focus:ring-violet-500 cursor-pointer"
                                            :checked="selected.includes('{{ $log->id }}')"
                                            @click="toggleRow('{{ $log->id }}')"
                                        >
                                    </td>
                                    <td class="px-5 py-3.5">
                                        <div class="flex flex-col">
                                            <span class="text-slate-700 dark:text-slate-300 font-medium text-xs" title="{{ $exactTime }}">{{ $timeAgo }}</span>
                                            <span class="text-[11px] text-slate-400 mt-0.5">{{ $log->created_at?->format('h:i A') }}</span>
                                        </div>
                                    </td>
                                    <td class="px-5 py-3.5">
                                        <span class="text-slate-800 dark:text-slate-200 font-medium">{{ $log->title }}</span>
                                        <div class="md:hidden mt-1">
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400">{{ $log->module }}</span>
                                        </div>
                                        <div class="sm:hidden mt-1.5 flex items-center gap-1.5">
                                            <div class="w-5 h-5 rounded-full bg-gradient-to-br from-violet-400 to-indigo-500 flex items-center justify-center text-white text-[9px] font-bold shrink-0">
                                                {{ strtoupper(substr($log->causer_name, 0, 1)) }}
                                            </div>
                                            <span class="text-xs text-slate-600 dark:text-slate-400 truncate">{{ $log->causer_name }}</span>
                                        </div>
                                    </td>
                                    <td class="px-5 py-3.5 hidden md:table-cell">
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-medium bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400">
                                            <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                                            {{ $log->module }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-3.5 hidden lg:table-cell">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold {{ $actionColor['bg'] }} {{ $actionColor['text'] }} border {{ $actionColor['border'] }}">
                                            {{ ucfirst($log->action) }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-3.5 hidden sm:table-cell">
                                        <div class="flex items-center gap-2">
                                            <div class="w-7 h-7 rounded-full bg-gradient-to-br from-violet-400 to-indigo-500 flex items-center justify-center text-white text-[10px] font-bold shrink-0">
                                                {{ strtoupper(substr($log->causer_name, 0, 1)) }}
                                            </div>
                                            <span class="text-slate-600 dark:text-slate-400 truncate max-w-[120px]">{{ $log->causer_name }}</span>
                                        </div>
                                    </td>
                                    <td class="px-5 py-3.5 hidden lg:table-cell">
                                        @if ($log->role)
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium bg-indigo-50 dark:bg-indigo-950/50 text-indigo-600 dark:text-indigo-400 border border-indigo-100 dark:border-indigo-900">
                                                {{ ucfirst($log->role) }}
                                            </span>
                                        @else
                                            <span class="text-slate-300 dark:text-slate-600">—</span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-3.5 hidden xl:table-cell">
                                        <span class="text-slate-500 dark:text-slate-400 truncate max-w-[140px] block">{{ $log->subject_name }}</span>
                                    </td>
                                    <td class="px-5 py-3.5">
                                        <div class="flex items-center justify-center gap-1">
                                            <a
                                                href="{{ route('activitylog.view', $log->id) }}"
                                                class="p-1.5 rounded-lg text-slate-400 hover:text-violet-600 hover:bg-violet-50 dark:hover:bg-violet-950/40 transition-all"
                                                title="View details"
                                            >
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                            </a>
                                            <form action="{{ route('activitylog.destroy', $log->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this activity log?');">
                                                @csrf
                                                @method('DELETE')
                                                <button
                                                    type="submit"
                                                    class="p-1.5 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 dark:hover:bg-rose-950/40 transition-all"
                                                    title="Delete"
                                                >
                                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="px-5 py-20 text-center">
                                        <div class="flex flex-col items-center gap-3">
                                            <div class="w-16 h-16 rounded-2xl bg-slate-100 dark:bg-slate-800 flex items-center justify-center">
                                                <svg class="w-8 h-8 text-slate-300 dark:text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m5.231 13.481L15 17.25m-4.5-15H5.625c-.621 0-1.125.504-1.125 1.125v16.5c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9zm3.75 11.625a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z"/></svg>
                                            </div>
                                            <p class="text-sm font-medium text-slate-500 dark:text-slate-400">No activity logs found</p>
                                            <p class="text-xs text-slate-400">Try adjusting your filters or date range</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($logs->hasPages())
                    <div class="border-t border-slate-100 dark:border-slate-800 px-5 py-4">
                        {{ $logs->links() }}
                    </div>
                @endif
            </div>
        </div>
    </main>
</div>
@endsection
