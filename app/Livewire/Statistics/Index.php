<?php

namespace App\Livewire\Statistics;

use App\Enums\RoleName;
use App\Livewire\Concerns\InteractsWithAuthenticatedUser;
use App\Models\Game;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Statistics')]
class Index extends Component
{
    use InteractsWithAuthenticatedUser;

    public function mount(): void
    {
        $this->authorize('collectAny', Game::class);
    }

    /**
     * @return Collection<int, Game>
     */
    #[Computed]
    public function games(): Collection
    {
        $games = Game::query()
            ->with(['team.season'])
            ->orderByDesc('date_time');

        if (! $this->actor()->hasRole(RoleName::Administrator)) {
            $games->whereHas('team', fn (Builder $query) => $query->where('club_id', $this->actor()->club_id));
        }

        return $games->get();
    }

    public function render(): View
    {
        return view('livewire.statistics.index');
    }
}
