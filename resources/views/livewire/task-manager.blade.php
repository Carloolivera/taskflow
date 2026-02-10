<x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
        {{ __('Tasks') }} - {{ $projectName }}
    </h2>
</x-slot>

<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">

            <div class="flex justify-between items-center mb-6">
                <div>
                    <a href="{{ route('projects.index') }}" class="text-blue-600 dark:text-blue-400 hover:underline text-sm">&larr; Volver a Proyectos</a>
                    <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200 mt-1">Tareas de {{ $projectName }}</h2>
                </div>
                <button wire:click="openModal" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
                    Crear Tarea
                </button>
            </div>

            @if (session()->has('message'))
                <div class="bg-green-100 dark:bg-green-900 border border-green-400 dark:border-green-600 text-green-700 dark:text-green-300 px-4 py-3 rounded mb-4">
                    {{ session('message') }}
                </div>
            @endif

            {{-- Filters --}}
            <div class="mb-4 grid grid-cols-1 md:grid-cols-5 gap-4">
                <div>
                    <input wire:model.live="search" type="text" placeholder="Buscar por título..."
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-gray-200">
                </div>
                <div>
                    <select wire:model.live="filterStatus"
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-gray-200">
                        <option value="">Todos los estados</option>
                        <option value="pending">Pendiente</option>
                        <option value="in_progress">En Progreso</option>
                        <option value="completed">Completada</option>
                    </select>
                </div>
                <div>
                    <select wire:model.live="filterPriority"
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-gray-200">
                        <option value="">Todas las prioridades</option>
                        <option value="low">Baja</option>
                        <option value="medium">Media</option>
                        <option value="high">Alta</option>
                        <option value="urgent">Urgente</option>
                    </select>
                </div>
                <div>
                    <select wire:model.live="filterTag"
                        class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-gray-200">
                        <option value="">Todos los tags</option>
                        @foreach($allTags as $tag)
                            <option value="{{ $tag->id }}">{{ $tag->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <button wire:click="clearFilters"
                        class="w-full px-4 py-2 bg-gray-200 dark:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-500">
                        Limpiar filtros
                    </button>
                </div>
            </div>

            {{-- Table --}}
            <div class="shadow-md rounded-lg overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Título</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Estado</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Prioridad</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Vencimiento</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Tags</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse ($tasks as $task)
                            <tr class="{{ $task->due_date && $task->due_date->isPast() && $task->status !== 'completed' ? 'bg-red-50 dark:bg-red-900/20' : '' }}">
                                <td class="px-6 py-4 text-gray-900 dark:text-gray-100">{{ $task->title }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 text-xs rounded-full
                                        @if($task->status === 'pending') bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300
                                        @elseif($task->status === 'in_progress') bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300
                                        @else bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300
                                        @endif">
                                        {{ str_replace('_', ' ', ucfirst($task->status)) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 text-xs rounded-full
                                        @if($task->priority === 'low') bg-gray-100 text-gray-800 dark:bg-gray-600 dark:text-gray-300
                                        @elseif($task->priority === 'medium') bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300
                                        @elseif($task->priority === 'high') bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-300
                                        @else bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300
                                        @endif">
                                        {{ ucfirst($task->priority) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm {{ $task->due_date && $task->due_date->isPast() && $task->status !== 'completed' ? 'text-red-600 dark:text-red-400 font-medium' : 'text-gray-700 dark:text-gray-300' }}">
                                    {{ $task->due_date ? $task->due_date->format('d/m/Y') : '-' }}
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-wrap gap-1">
                                        @foreach($task->tags as $tag)
                                            <span class="px-2 py-0.5 text-xs rounded-full text-white" style="background-color: {{ $tag->color }}">
                                                {{ $tag->name }}
                                            </span>
                                        @endforeach
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <button wire:click="edit({{ $task->id }})" class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300 mr-3">
                                        Editar
                                    </button>
                                    <button wire:click="confirmDelete({{ $task->id }})" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">
                                        Eliminar
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                                    No hay tareas en este proyecto. ¡Creá una nueva!
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $tasks->links() }}
            </div>
        </div>
    </div>

    {{-- Create/Edit Modal --}}
    @if ($showModal)
        <div class="fixed inset-0 bg-gray-600/50 overflow-y-auto h-full w-full z-50" wire:click="$set('showModal', false)">
            <div class="relative top-10 mx-auto p-5 border w-[480px] shadow-lg rounded-md bg-white dark:bg-gray-800 dark:border-gray-700" wire:click.stop>
                <h3 class="text-lg font-medium mb-4 text-gray-900 dark:text-gray-100">{{ $isEditing ? 'Editar' : 'Crear' }} Tarea</h3>

                <form wire:submit="save">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Título</label>
                        <input wire:model="title" type="text" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-gray-200">
                        @error('title') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Descripción</label>
                        <textarea wire:model="description" rows="3" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-gray-200"></textarea>
                        @error('description') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Estado</label>
                            <select wire:model="status" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-gray-200">
                                <option value="pending">Pendiente</option>
                                <option value="in_progress">En Progreso</option>
                                <option value="completed">Completada</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Prioridad</label>
                            <select wire:model="priority" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-gray-200">
                                <option value="low">Baja</option>
                                <option value="medium">Media</option>
                                <option value="high">Alta</option>
                                <option value="urgent">Urgente</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Fecha de vencimiento</label>
                        <input wire:model="due_date" type="date" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-gray-200">
                        @error('due_date') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Tags</label>
                        <div class="flex flex-wrap gap-2">
                            @foreach($allTags as $tag)
                                <label class="inline-flex items-center">
                                    <input wire:model="selectedTags" type="checkbox" value="{{ $tag->id }}" class="rounded border-gray-300 dark:border-gray-600 text-blue-600 focus:ring-blue-500 dark:bg-gray-700">
                                    <span class="ml-1 px-2 py-0.5 text-xs rounded-full text-white" style="background-color: {{ $tag->color }}">{{ $tag->name }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="flex justify-end gap-2">
                        <button type="button" wire:click="$set('showModal', false)" class="px-4 py-2 bg-gray-300 dark:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-md hover:bg-gray-400 dark:hover:bg-gray-500">
                            Cancelar
                        </button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                            {{ $isEditing ? 'Actualizar' : 'Crear' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    {{-- Delete Confirmation Modal --}}
    @if ($showDeleteModal)
        <div class="fixed inset-0 bg-gray-600/50 overflow-y-auto h-full w-full z-50 flex items-center justify-center">
            <div class="mx-auto p-6 border w-96 shadow-lg rounded-lg bg-white dark:bg-gray-800 dark:border-gray-700" wire:click.stop>
                <div class="flex items-center justify-center w-12 h-12 mx-auto mb-4 rounded-full bg-red-100 dark:bg-red-900/30">
                    <svg class="w-6 h-6 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-center text-gray-900 dark:text-gray-100 mb-2">Confirmar eliminación</h3>
                <p class="text-sm text-center text-gray-600 dark:text-gray-400 mb-6">
                    ¿Estás seguro de que querés eliminar <strong class="text-gray-900 dark:text-gray-100">{{ $deleteTaskTitle }}</strong>?
                </p>
                <div class="flex justify-center gap-3">
                    <button wire:click="cancelDelete" class="px-4 py-2 bg-gray-200 dark:bg-gray-600 text-gray-700 dark:text-gray-300 rounded-md hover:bg-gray-300 dark:hover:bg-gray-500 text-sm font-medium">
                        Cancelar
                    </button>
                    <button wire:click="delete" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 text-sm font-medium">
                        Sí, eliminar
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
