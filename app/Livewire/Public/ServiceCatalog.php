<?php

namespace App\Livewire\Public;

use App\Models\Bidang;
use App\Models\Service;
use Livewire\Component;
use Livewire\WithPagination;

class ServiceCatalog extends Component
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

        $services = Service::published()
            ->with('bidang')
            ->when($this->search, fn ($query) => $query->where(fn ($q) => $q
                ->where('name', 'like', '%'.escapeLike($this->search).'%')
                ->orWhere('short_description', 'like', '%'.escapeLike($this->search).'%')))
            ->when($this->bidangId, fn ($query) => $query->where('bidang_id', $this->bidangId))
            ->orderBy('name')
            ->paginate(9);

        return view('livewire.public.service-catalog', compact('bidangs', 'services'));
    }
}
