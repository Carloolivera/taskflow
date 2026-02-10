<x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
        {{ __('Dashboard') }}
    </h2>
</x-slot>

<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

        {{-- Stats Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            {{-- Total Projects --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-blue-100 dark:bg-blue-900">
                        <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Proyectos</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $totalProjects }}</p>
                        <p class="text-xs text-green-600 dark:text-green-400">{{ $activeProjects }} activos</p>
                    </div>
                </div>
            </div>

            {{-- Total Tasks --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-indigo-100 dark:bg-indigo-900">
                        <svg class="w-6 h-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Tareas</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $totalTasks }}</p>
                    </div>
                </div>
            </div>

            {{-- Pending Tasks --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-yellow-100 dark:bg-yellow-900">
                        <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Pendientes</p>
                        <p class="text-2xl font-bold text-yellow-600 dark:text-yellow-400">{{ $pendingTasks }}</p>
                        <p class="text-xs text-blue-600 dark:text-blue-400">{{ $inProgressTasks }} en progreso</p>
                    </div>
                </div>
            </div>

            {{-- Overdue / Admin Stats --}}
            @if($overdueTasks > 0)
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-red-100 dark:bg-red-900">
                            <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4.5c-.77-.833-2.694-.833-3.464 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Vencidas</p>
                            <p class="text-2xl font-bold text-red-600 dark:text-red-400">{{ $overdueTasks }}</p>
                        </div>
                    </div>
                </div>
            @else
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <div class="flex items-center">
                        <div class="p-3 rounded-full bg-green-100 dark:bg-green-900">
                            <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Completadas</p>
                            <p class="text-2xl font-bold text-green-600 dark:text-green-400">{{ $completedTasks }}</p>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- Recent Tasks --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Tareas Recientes</h3>

                @if($recentTasks->count() > 0)
                    <ul class="space-y-3">
                        @foreach($recentTasks as $task)
                            <li class="flex items-center justify-between border-b border-gray-100 dark:border-gray-700 pb-3 last:border-0">
                                <div>
                                    <p class="font-medium text-gray-900 dark:text-gray-100">{{ $task->title }}</p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ $task->project->name }}
                                    </p>
                                </div>
                                <div class="text-right">
                                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                        @if($task->status === 'completed') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300
                                        @elseif($task->status === 'in_progress') bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300
                                        @else bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300
                                        @endif">
                                        @if($task->status === 'completed') Completada
                                        @elseif($task->status === 'in_progress') En Progreso
                                        @else Pendiente
                                        @endif
                                    </span>
                                    <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">{{ $task->created_at->diffForHumans() }}</p>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p class="text-gray-500 dark:text-gray-400 text-center py-4">No hay tareas todavia.</p>
                @endif
            </div>

            {{-- Quick Actions --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">Acciones Rapidas</h3>

                <div class="space-y-3">
                    <a href="{{ route('projects.index') }}" wire:navigate
                        class="flex items-center p-3 rounded-lg border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                        <div class="p-2 rounded-full bg-blue-100 dark:bg-blue-900 mr-3">
                            <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"/>
                            </svg>
                        </div>
                        <span class="text-gray-900 dark:text-gray-100 font-medium">Gestionar Proyectos</span>
                    </a>

                    @if($isAdmin)
                        <a href="{{ route('tags.index') }}" wire:navigate
                            class="flex items-center p-3 rounded-lg border border-gray-200 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                            <div class="p-2 rounded-full bg-purple-100 dark:bg-purple-900 mr-3">
                                <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                                </svg>
                            </div>
                            <span class="text-gray-900 dark:text-gray-100 font-medium">Gestionar Etiquetas</span>
                        </a>
                    @endif
                </div>

                @if($isAdmin)
                    <div class="mt-6 pt-4 border-t border-gray-200 dark:border-gray-700">
                        <div class="flex justify-between text-sm text-gray-500 dark:text-gray-400">
                            <span>Total usuarios: <span class="font-semibold text-gray-900 dark:text-gray-100">{{ $totalUsers }}</span></span>
                            <span>Etiquetas: <span class="font-semibold text-gray-900 dark:text-gray-100">{{ $totalTags }}</span></span>
                        </div>
                    </div>
                @endif
            </div>
        </div>

    </div>
</div>
