<?php

namespace App\Livewire\Public;

use App\Models\Bidang;
use App\Models\SopDocument;
use App\Support\PublicCache;
use Livewire\Component;
use Livewire\WithPagination;

class SopCatalog extends Component
{
    use WithPagination;

    public string $search = '';

    public ?string $bidangId = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'bidangId' => ['except' => null],
    ];

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function setBidang(?string $id): void
    {
        $this->bidangId = $id;
        $this->resetPage();
    }

    public function render()
    {
        $bidangs = PublicCache::remember(PublicCache::SOPS_BIDANGS, fn () => Bidang::orderBy('name')->get(), [PublicCache::TAG_SOPS]);

        $catalogKey = PublicCache::keyFor('sops.catalog', [
            'page' => $this->getPage(),
            'bidang' => (string) $this->bidangId,
            'search' => $this->search,
        ]);

        $sops = PublicCache::remember($catalogKey, fn () => SopDocument::published()
            ->with('bidang')
            ->when($this->search, fn ($query) => $query->where(fn ($q) => $q
                ->where('title', 'like', '%'.escapeLike($this->search).'%')
                ->orWhere('sop_number', 'like', '%'.escapeLike($this->search).'%')))
            ->when($this->bidangId, fn ($query) => $query->where('bidang_id', $this->bidangId))
            ->orderBy('title')
            ->paginate(10), [PublicCache::TAG_SOPS]);

        return view('livewire.public.sop-catalog', compact('bidangs', 'sops'));
    }
}
