<?php

namespace App\Livewire\Seasons;

use App\Actions\UpdateSeason as UpdateSeasonAction;
use App\Models\Season;
use Flux\Flux;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Edit season')]
class Edit extends Component
{
    #[Locked]
    public Season $season;

    public string $name = '';

    public function mount(Season $season): void
    {
        $this->authorize('update', $season);

        $this->season = $season;
        $this->name = $season->name;
    }

    public function save(UpdateSeasonAction $updateSeason): void
    {
        $this->authorize('update', $this->season);

        $validated = $this->validate([
            'name' => Season::nameRules($this->season->id),
        ]);

        $updateSeason->handle($this->season, $validated['name']);

        Flux::toast(variant: 'success', text: __('Season updated.'));

        $this->redirect(route('seasons.index'), navigate: true);
    }
}
