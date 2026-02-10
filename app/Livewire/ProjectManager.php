<?php

namespace App\Livewire;

use App\Models\Project;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class ProjectManager extends Component
{
    use WithPagination;

    public $projectId;
    public $name;
    public $description;
    public $status = 'active';

    public $isEditing = false;
    public $showModal = false;
    public $search = '';

    public $showDeleteModal = false;
    public $deleteProjectId = null;
    public $deleteProjectName = '';

    public $filterStatus = '';

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedFilterStatus()
    {
        $this->resetPage();
    }

    public function clearFilters()
    {
        $this->reset(['search', 'filterStatus']);
        $this->resetPage();
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:2000',
            'status' => 'required|in:active,completed,archived',
        ];
    }

    public function openModal()
    {
        $this->resetValidation();
        $this->reset(['name', 'description', 'status', 'isEditing', 'projectId']);
        $this->status = 'active';
        $this->showModal = true;
    }

    public function edit($id)
    {
        $project = Project::where('user_id', auth()->id())->findOrFail($id);
        $this->isEditing = true;
        $this->projectId = $project->id;
        $this->name = $project->name;
        $this->description = $project->description;
        $this->status = $project->status;
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate();

        if ($this->isEditing) {
            $project = Project::where('user_id', auth()->id())->findOrFail($this->projectId);
            $project->update([
                'name' => $this->name,
                'description' => $this->description,
                'status' => $this->status,
            ]);
            session()->flash('message', 'Proyecto actualizado exitosamente.');
        } else {
            Project::create([
                'name' => $this->name,
                'description' => $this->description,
                'status' => $this->status,
                'user_id' => auth()->id(),
            ]);
            session()->flash('message', 'Proyecto creado exitosamente.');
        }

        $this->showModal = false;
        $this->reset(['name', 'description', 'status', 'isEditing', 'projectId']);
    }

    public function confirmDelete($id)
    {
        $project = Project::where('user_id', auth()->id())->findOrFail($id);
        $this->deleteProjectId = $id;
        $this->deleteProjectName = $project->name;
        $this->showDeleteModal = true;
    }

    public function cancelDelete()
    {
        $this->showDeleteModal = false;
        $this->deleteProjectId = null;
        $this->deleteProjectName = '';
    }

    public function delete()
    {
        Project::where('user_id', auth()->id())->findOrFail($this->deleteProjectId)->delete();
        $this->showDeleteModal = false;
        $this->deleteProjectId = null;
        $this->deleteProjectName = '';
        session()->flash('message', 'Proyecto eliminado exitosamente.');
    }

    public function render()
    {
        return view('livewire.project-manager', [
            'projects' => Project::query()
                ->where('user_id', auth()->id())
                ->withCount('tasks')
                ->when($this->search, fn ($query) =>
                    $query->where('name', 'like', '%' . $this->search . '%')
                )
                ->when($this->filterStatus !== '', fn ($query) =>
                    $query->where('status', $this->filterStatus)
                )
                ->latest()
                ->paginate(10),
        ]);
    }
}
