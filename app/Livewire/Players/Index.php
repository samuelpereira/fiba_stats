<?php

namespace App\Livewire\Players;

use App\Actions\DeletePlayer;
use App\Enums\PermissionName;
use App\Livewire\Concerns\ConfirmsDeletion;
use App\Livewire\Concerns\InteractsWithAuthenticatedUser;
use App\Models\Player;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Players')]
class Index extends Component
{
    use ConfirmsDeletion, InteractsWithAuthenticatedUser;

    public function mount(): void
    {
        $this->authorize('viewAny', Player::class);
    }

    /**
     * @return Collection<int, Player>
     */
    #[Computed]
    public function players(): Collection
    {
        $actor = $this->actor();

        $query = Player::query()->with(['team.club', 'team.season'])->orderBy('order')->orderBy('name');

        if (! $actor->can(PermissionName::CreateClubs->value)) {
            $query->whereHas('team', fn (Builder $teamQuery) => $teamQuery->where('club_id', $actor->club_id));
        }

        return $query->get();
    }

    protected function deletionModalName(): string
    {
        return 'confirm-delete';
    }

    protected function recordForDeletion(int $id): Model
    {
        return Player::query()->findOrFail($id);
    }

    protected function performDeletion(Model $record): void
    {
        if (! $record instanceof Player) {
            return;
        }

        (new DeletePlayer)->handle($record);
    }

    protected function deletedMessage(): string
    {
        return __('Player deleted.');
    }

    protected function clearListedRecords(): void
    {
        unset($this->players);
    }

    public function render(): View
    {
        return view('livewire.players.index');
    }
}
