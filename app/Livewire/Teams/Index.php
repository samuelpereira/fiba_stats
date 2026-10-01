<?php

namespace App\Livewire\Teams;

use App\Actions\DeleteTeam;
use App\Enums\PermissionName;
use App\Livewire\Concerns\ConfirmsDeletion;
use App\Livewire\Concerns\InteractsWithAuthenticatedUser;
use App\Models\Team;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Teams')]
class Index extends Component
{
    use ConfirmsDeletion, InteractsWithAuthenticatedUser;

    public function mount(): void
    {
        $this->authorize('viewAny', Team::class);
    }

    /**
     * @return Collection<int, Team>
     */
    #[Computed]
    public function teams(): Collection
    {
        $actor = $this->actor();

        $query = Team::query()->with(['club', 'season'])->orderBy('order')->orderBy('name');

        if (! $actor->can(PermissionName::CreateClubs->value)) {
            $query->where('club_id', $actor->club_id);
        }

        return $query->get();
    }

    protected function deletionModalName(): string
    {
        return 'confirm-delete';
    }

    protected function recordForDeletion(int $id): Model
    {
        return Team::query()->findOrFail($id);
    }

    protected function performDeletion(Model $record): void
    {
        if (! $record instanceof Team) {
            return;
        }

        (new DeleteTeam)->handle($record);
    }

    protected function deletedMessage(): string
    {
        return __('Team deleted.');
    }

    protected function clearListedRecords(): void
    {
        unset($this->teams);
    }

    public function render(): View
    {
        return view('livewire.teams.index');
    }
}
