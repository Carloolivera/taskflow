<?php

namespace App\Livewire;

use App\Models\Project;
use App\Models\Tag;
use App\Models\Task;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class TaskManager extends Component
{
    use WithPagination;

    public $project;
    public $projectName;

    public $taskId;
    public $title;
    public $description;
    public $status = 'pending';
    public $priority = 'medium';
    public $due_date;
    public $selectedTags = [];

    public $isEditing = false;
    public $showModal = false;
    public $search = '';

    public $showDeleteModal = false;
    public $deleteTaskId = null;
    public $deleteTaskTitle = '';

    public $filterStatus = '';
    public $filterPriority = '';
    public $filterTag = '';

    public function mount(Project $project)
    {
        if ($project->user_id !== auth()->id()) {
            abort(403);
        }

        $this->project = $project;
        $this->projectName = $project->name;
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedFilterStatus()
    {
        $this->resetPage();
    }

    public function updatedFilterPriority()
    {
        $this->resetPage();
    }

    public function updatedFilterTag()
    {
        $this->resetPage();
    }

    public function clearFilters()
    {
        $this->reset(['search', 'filterStatus', 'filterPriority', 'filterTag']);
        $this->resetPage();
    }

    public function rules()
    {
        return [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:5000',
            'status' => 'required|in:pending,in_progress,completed',
            'priority' => 'required|in:low,medium,high,urgent',
            'due_date' => 'nullable|date',
            'selectedTags' => 'array',
            'selectedTags.*' => 'exists:tags,id',
        ];
    }

    public function openModal()
    {
        $this->resetValidation();
        $this->reset(['title', 'description', 'status', 'priority', 'due_date', 'selectedTags', 'isEditing', 'taskId']);
        $this->status = 'pending';
        $this->priority = 'medium';
        $this->showModal = true;
    }

    public function edit($id)
    {
        $task = Task::where('project_id', $this->project->id)->findOrFail($id);
        $this->isEditing = true;
        $this->taskId = $task->id;
        $this->title = $task->title;
        $this->description = $task->description;
        $this->status = $task->status;
        $this->priority = $task->priority;
        $this->due_date = $task->due_date?->format('Y-m-d');
        $this->selectedTags = $task->tags->pluck('id')->toArray();
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate();

        if ($this->isEditing) {
            $task = Task::where('project_id', $this->project->id)->findOrFail($this->taskId);
            $task->update([
                'title' => $this->title,
                'description' => $this->description,
                'status' => $this->status,
                'priority' => $this->priority,
                'due_date' => $this->due_date ?: null,
            ]);
            $task->tags()->sync($this->selectedTags);
            session()->flash('message', 'Tarea actualizada exitosamente.');
        } else {
            $task = Task::create([
                'title' => $this->title,
                'description' => $this->description,
                'status' => $this->status,
                'priority' => $this->priority,
                'due_date' => $this->due_date ?: null,
                'project_id' => $this->project->id,
                'user_id' => auth()->id(),
            ]);
            $task->tags()->sync($this->selectedTags);
            session()->flash('message', 'Tarea creada exitosamente.');
        }

        $this->showModal = false;
        $this->reset(['title', 'description', 'status', 'priority', 'due_date', 'selectedTags', 'isEditing', 'taskId']);
    }

    public function confirmDelete($id)
    {
        $task = Task::where('project_id', $this->project->id)->findOrFail($id);
        $this->deleteTaskId = $id;
        $this->deleteTaskTitle = $task->title;
        $this->showDeleteModal = true;
    }

    public function cancelDelete()
    {
        $this->showDeleteModal = false;
        $this->deleteTaskId = null;
        $this->deleteTaskTitle = '';
    }

    public function delete()
    {
        Task::where('project_id', $this->project->id)->findOrFail($this->deleteTaskId)->delete();
        $this->showDeleteModal = false;
        $this->deleteTaskId = null;
        $this->deleteTaskTitle = '';
        session()->flash('message', 'Tarea eliminada exitosamente.');
    }

    public function render()
    {
        return view('livewire.task-manager', [
            'tasks' => Task::query()
                ->where('project_id', $this->project->id)
                ->with('tags')
                ->when($this->search, fn ($query) =>
                    $query->where('title', 'like', '%' . $this->search . '%')
                )
                ->when($this->filterStatus !== '', fn ($query) =>
                    $query->where('status', $this->filterStatus)
                )
                ->when($this->filterPriority !== '', fn ($query) =>
                    $query->where('priority', $this->filterPriority)
                )
                ->when($this->filterTag !== '', fn ($query) =>
                    $query->whereHas('tags', fn ($tq) => $tq->where('tags.id', $this->filterTag))
                )
                ->latest()
                ->paginate(10),
            'allTags' => Tag::all(),
        ]);
    }
}
