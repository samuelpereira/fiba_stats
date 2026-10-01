<?php

namespace App\Livewire\Users;

use App\Actions\DeleteUser;
use App\Enums\PermissionName;
use App\Enums\RoleName;
use App\Livewire\Concerns\ConfirmsDeletion;
use App\Livewire\Concerns\InteractsWithAuthenticatedUser;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Users')]
class Index extends Component
{
    use ConfirmsDeletion, InteractsWithAuthenticatedUser;

    public function mount(): void
    {
        $this->authorize('viewAny', User::class);
    }

    /**
     * @return Collection<int, User>
     */
    #[Computed]
    public function users(): Collection
    {
        $actor = $this->actor();

        $query = User::query()->role(RoleName::User)->with('club')->orderBy('name');

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
        return User::query()->findOrFail($id);
    }

    protected function performDeletion(Model $record): void
    {
        if (! $record instanceof User) {
            return;
        }

        (new DeleteUser)->handle($this->actor(), $record);
    }

    protected function deletedMessage(): string
    {
        return __('User deleted.');
    }

    protected function clearListedRecords(): void
    {
        unset($this->users);
    }

    public function render(): View
    {
        return view('livewire.users.index');
    }
}
