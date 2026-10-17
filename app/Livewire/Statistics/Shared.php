<?php

namespace App\Livewire\Statistics;

use App\Models\Game;
use Livewire\Attributes\Layout;

#[Layout('layouts.collect', ['titleAsGiven' => true])]
class Shared extends Show
{
    public function mount(Game $game): void
    {
        abort_if($game->share_token === null, 404);

        $this->shared = true;
        $this->game = $game->loadMissing('team');
        $this->backUrl = '';
        $this->mobileShotChartFilter = $this->defaultMobileShotChartFilter();
    }
}
