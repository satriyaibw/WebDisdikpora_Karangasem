<?php

namespace App\Livewire\Public;

use App\Models\Bidang;
use App\Models\SopDocument;
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
        $bidangs = Bidang::orderBy('name')->get();

        $sops = SopDocument::published()
            ->with('bidang')
            ->when($this->search, fn ($query) => $query->where(fn ($q) => $q
                ->where('title', 'like', "%{$this->search}%")
                ->orWhere('sop_number', 'like', "%{$this->search}%")))
            ->when($this->bidangId, fn ($query) => $query->where('bidang_id', $this->bidangId))
            ->orderBy('title')
            ->paginate(10);

        return view('livewire.public.sop-catalog', compact('bidangs', 'sops'));
    }
}
