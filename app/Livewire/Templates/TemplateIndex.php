<?php

namespace App\Livewire\Templates;

use App\Models\MessageTemplate;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Computed;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;

#[Layout('components.layouts.tenant')]
#[Title('Message Templates')]
class TemplateIndex extends Component
{
    use WithPagination;

    public string $search = '';
    public string $filterCategory = '';

    public bool $showModal = false;
    public bool $isEditing = false;
    public ?int $editingId = null;

    // Form fields
    public string $name = '';
    public string $content = '';
    public string $category = '';
    public bool $is_favorite = false;

    // Preview
    public bool $showPreview = false;
    public string $previewName = 'John Doe';
    public string $previewPhone = '6281234567890';

    protected $rules = [
        'name' => 'required|min:2|max:100',
        'content' => 'required|min:1',
        'category' => 'nullable|max:50',
    ];

    #[Computed]
    public function categories()
    {
        return Auth::user()->messageTemplates()
            ->whereNotNull('category')
            ->distinct()
            ->pluck('category')
            ->filter()
            ->values();
    }

    #[Computed]
    public function variables()
    {
        return MessageTemplate::getAvailableVariables();
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function openModal()
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function resetForm()
    {
        $this->isEditing = false;
        $this->editingId = null;
        $this->name = '';
        $this->content = '';
        $this->category = '';
        $this->is_favorite = false;
        $this->showPreview = false;
        $this->resetValidation();
    }

    public function save()
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'content' => $this->content,
            'category' => $this->category ?: null,
            'is_favorite' => $this->is_favorite,
        ];

        if ($this->isEditing && $this->editingId) {
            $template = Auth::user()->messageTemplates()->findOrFail($this->editingId);
            $template->update($data);
            $message = 'Template updated!';
        } else {
            Auth::user()->messageTemplates()->create($data);
            $message = 'Template created!';
        }

        $this->closeModal();
        $this->dispatch('notify', ['type' => 'success', 'message' => $message]);
    }

    public function edit($id)
    {
        $template = Auth::user()->messageTemplates()->findOrFail($id);

        $this->isEditing = true;
        $this->editingId = $template->id;
        $this->name = $template->name;
        $this->content = $template->content;
        $this->category = $template->category ?? '';
        $this->is_favorite = $template->is_favorite;
        $this->showModal = true;
    }

    public function duplicate($id)
    {
        $template = Auth::user()->messageTemplates()->findOrFail($id);

        Auth::user()->messageTemplates()->create([
            'name' => $template->name . ' (Copy)',
            'content' => $template->content,
            'category' => $template->category,
            'is_favorite' => false,
        ]);

        $this->dispatch('notify', ['type' => 'success', 'message' => 'Template duplicated!']);
    }

    public function toggleFavorite($id)
    {
        $template = Auth::user()->messageTemplates()->findOrFail($id);
        $template->update(['is_favorite' => !$template->is_favorite]);
    }

    public function delete($id)
    {
        Auth::user()->messageTemplates()->findOrFail($id)->delete();
        $this->dispatch('notify', ['type' => 'success', 'message' => 'Template deleted!']);
    }

    public function insertVariable($variable)
    {
        $this->dispatch('insert-variable', variable: $variable);
    }

    public function getPreviewContent()
    {
        $template = new MessageTemplate(['content' => $this->content]);
        return $template->parseVariables([
            'name' => $this->previewName,
            'phone' => $this->previewPhone,
            'email' => 'john@example.com',
            'company' => 'PT Example',
        ]);
    }

    public function render()
    {
        $query = Auth::user()->messageTemplates();

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                    ->orWhere('content', 'like', "%{$this->search}%");
            });
        }

        if ($this->filterCategory) {
            $query->where('category', $this->filterCategory);
        }

        $templates = $query->orderByDesc('is_favorite')
            ->latest()
            ->paginate(12);

        return view('livewire.templates.template-index', [
            'templates' => $templates,
        ]);
    }
}
