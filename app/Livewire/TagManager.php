<?php

namespace App\Livewire;

use App\Models\Tag;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class TagManager extends Component
{
    use WithPagination;

    public $tagId;
    public $name;
    public $color = '#3B82F6';

    public $isEditing = false;
    public $showModal = false;
    public $search = '';

    public $showDeleteModal = false;
    public $deleteTagId = null;
    public $deleteTagName = '';

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:50|unique:tags,name,' . ($this->tagId ?? 'NULL'),
            'color' => 'required|string|regex:/^#[0-9A-Fa-f]{6}$/',
        ];
    }

    public function openModal()
    {
        $this->resetValidation();
        $this->reset(['name', 'color', 'isEditing', 'tagId']);
        $this->color = '#3B82F6';
        $this->showModal = true;
    }

    public function edit($id)
    {
        $tag = Tag::findOrFail($id);
        $this->isEditing = true;
        $this->tagId = $tag->id;
        $this->name = $tag->name;
        $this->color = $tag->color;
        $this->showModal = true;
    }

    public function save()
    {
        $this->validate();

        if ($this->isEditing) {
            $tag = Tag::findOrFail($this->tagId);
            $tag->update([
                'name' => $this->name,
                'color' => $this->color,
            ]);
            session()->flash('message', 'Tag actualizado exitosamente.');
        } else {
            Tag::create([
                'name' => $this->name,
                'color' => $this->color,
            ]);
            session()->flash('message', 'Tag creado exitosamente.');
        }

        $this->showModal = false;
        $this->reset(['name', 'color', 'isEditing', 'tagId']);
    }

    public function confirmDelete($id)
    {
        $tag = Tag::findOrFail($id);
        $this->deleteTagId = $id;
        $this->deleteTagName = $tag->name;
        $this->showDeleteModal = true;
    }

    public function cancelDelete()
    {
        $this->showDeleteModal = false;
        $this->deleteTagId = null;
        $this->deleteTagName = '';
    }

    public function delete()
    {
        Tag::findOrFail($this->deleteTagId)->delete();
        $this->showDeleteModal = false;
        $this->deleteTagId = null;
        $this->deleteTagName = '';
        session()->flash('message', 'Tag eliminado exitosamente.');
    }

    public function render()
    {
        return view('livewire.tag-manager', [
            'tags' => Tag::query()
                ->when($this->search, fn ($query) =>
                    $query->where('name', 'like', '%' . $this->search . '%')
                )
                ->withCount('tasks')
                ->latest()
                ->paginate(10),
        ]);
    }
}
