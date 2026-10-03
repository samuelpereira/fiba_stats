<?php

namespace App\Livewire\Games;

use App\Actions\DeleteGame;
use App\Enums\PermissionName;
use App\Livewire\Concerns\ConfirmsDeletion;
use App\Livewire\Concerns\InteractsWithAuthenticatedUser;
use App\Models\Game;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Games')]
class Index extends Component
{
    use ConfirmsDeletion, InteractsWithAuthenticatedUser;

    public function mount(): void
    {
        $this->authorize('viewAny', Game::class);
    }

    /**
     * @return Collection<int, Game>
     */
    #[Computed]
    public function games(): Collection
    {
        $actor = $this->actor();

        $query = Game::query()->with(['team.club', 'team.season'])->orderByDesc('date_time');

        if (! $actor->can(PermissionName::CreateClubs->value)) {
            $query->whereHas('team', fn (Builder $teamQuery) => $teamQuery->where('club_id', $actor->club_id));
        }

        return $query->get();
    }

    public function render(): View
    {
        return view('livewire.games.index');
    }

    protected function deletionModalName(): string
    {
        return 'confirm-delete';
    }

    protected function recordForDeletion(int|string $key): Model
    {
        return Game::findByPublicKey($key);
    }

    protected function performDeletion(Model $record): void
    {
        if (! $record instanceof Game) {
            return;
        }

        (new DeleteGame)->handle($record);
    }

    protected function deletedMessage(): string
    {
        return __('Game deleted.');
    }

    protected function clearListedRecords(): void
    {
        unset($this->games);
    }
}
