<?php

namespace App\Livewire\Clubs;

use App\Models\Club;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Clubs')]
class Index extends Component
{
    public function mount(): void
    {
        $this->authorize('viewAny', Club::class);
    }

    /**
     * @return Collection<int, Club>
     */
    #[Computed]
    public function clubs(): Collection
    {
        return Club::query()->with('access')->orderBy('name')->get();
    }

    public function render(): View
    {
        return view('livewire.clubs.index');
    }
}
