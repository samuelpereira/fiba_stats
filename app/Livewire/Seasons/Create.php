<?php

namespace App\Livewire\Seasons;

use App\Actions\CreateSeason as CreateSeasonAction;
use App\Models\Season;
use Flux\Flux;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('New season')]
class Create extends Component
{
    public string $name = '';

    public function mount(): void
    {
        $this->authorize('create', Season::class);
    }

    public function save(CreateSeasonAction $createSeason): void
    {
        $this->authorize('create', Season::class);

        $validated = $this->validate([
            'name' => Season::nameRules(),
        ]);

        $createSeason->handle($validated['name']);

        Flux::toast(variant: 'success', text: __('Season created.'));

        $this->redirect(route('seasons.index'), navigate: true);
    }
}
