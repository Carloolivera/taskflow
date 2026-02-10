<x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
        {{ __('Tags') }}
    </h2>
</x-slot>

<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">

            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-bold text-gray-800 dark:text-gray-200">Gestión de Tags</h2>
                <button wire:click="openModal" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
                    Crear Tag
                </button>
            </div>

            @if (session()->has('message'))
                <div class="bg-green-100 dark:bg-green-900 border border-green-400 dark:border-green-600 text-green-700 dark:text-green-300 px-4 py-3 rounded mb-4">
                    {{ session('message') }}
                </div>
            @endif

            <div class="mb-4">
                <input wire:model.live="search" type="text" placeholder="Buscar por nombre..."
                    class="w-full md:w-1/3 px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-gray-200">
            </div>

            <div class="shadow-md rounded-lg overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Color</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Nombre</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Tareas</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse ($tags as $tag)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-block w-6 h-6 rounded-full" style="background-color: {{ $tag->color }}"></span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 text-xs rounded-full text-white font-medium" style="background-color: {{ $tag->color }}">
                                        {{ $tag->name }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-gray-700 dark:text-gray-300">{{ $tag->tasks_count }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <button wire:click="edit({{ $tag->id }})" class="text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300 mr-3">
                                        Editar
                                    </button>
                                    <button wire:click="confirmDelete({{ $tag->id }})" class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">
                                        Eliminar
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                                    No hay tags. ¡Creá uno nuevo!
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $tags->links() }}
            </div>

        </div>
    </div>

    @if ($showModal)
        <div class="fixed inset-0 bg-gray-600/50 overflow-y-auto h-full w-full z-50" wire:click="$set('showModal', false)">
            <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white dark:bg-gray-800 dark:border-gray-700" wire:click.stop>
                <h3 class="text-lg font-medium mb-4 text-gray-900 dark:text-gray-100">{{ $isEditing ? 'Editar' : 'Crear' }} Tag</h3>

                <form wire:submit="save">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Nombre</label>
                        <input wire:model="name" type="text" class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-gray-200">
                        @error('name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Color</label>
                        <div class="flex items-center gap-3">
                            <input wire:model="color" type="color" class="h-10 w-14 rounded border border-gray-300 dark:border-gray-600 cursor-pointer">
                            <input wire:model="color" type="text" class="flex-1 px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-gray-200" placeholder="#3B82F6">
                        </div>
                        @error('color') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
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
                    ¿Estás seguro de que querés eliminar el tag <strong class="text-gray-900 dark:text-gray-100">{{ $deleteTagName }}</strong>?
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
