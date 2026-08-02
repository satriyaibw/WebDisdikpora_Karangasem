<?php

namespace App\Livewire\Public;

use App\Models\PpidCategory;
use Livewire\Component;

class PpidTabs extends Component
{
    public ?string $activeCategorySlug = null;

    public string $search = '';

    public function mount(): void
    {
        $this->activeCategorySlug ??= PpidCategory::query()
            ->orderBy('id')
            ->value('slug');
    }

    public function setCategory(string $slug): void
    {
        $this->activeCategorySlug = $slug;
        $this->search = '';
    }

    public function render()
    {
        $categories = PpidCategory::withCount([
            'documents' => fn ($query) => $query->published(),
        ])->orderBy('id')->get();

        $documents = PpidCategory::query()
            ->where('slug', $this->activeCategorySlug)
            ->with(['documents' => fn ($query) => $query
                ->published()
                ->when($this->search, fn ($q) => $q->where(fn ($inner) => $inner
                    ->where('title', 'like', '%'.escapeLike($this->search).'%')
                    ->orWhere('doc_number', 'like', '%'.escapeLike($this->search).'%')))
                ->orderByDesc('year')
                ->orderBy('title')])
            ->first();

        return view('livewire.public.ppid-tabs', compact('categories', 'documents'));
    }
}
