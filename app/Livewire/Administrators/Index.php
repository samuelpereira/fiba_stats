<?php

namespace App\Livewire\Administrators;

use App\Actions\DeleteUser;
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

#[Title('Administrators')]
class Index extends Component
{
    use ConfirmsDeletion, InteractsWithAuthenticatedUser;

    public function mount(): void
    {
        $this->authorize('viewAnyStaff', User::class);
    }

    /**
     * @return Collection<int, User>
     */
    #[Computed]
    public function administrators(): Collection
    {
        return User::query()
            ->role(RoleName::staff())
            ->with('roles')
            ->orderBy('name')
            ->get();
    }

    protected function deletionModalName(): string
    {
        return 'confirm-delete';
    }

    protected function recordForDeletion(int $id): Model
    {
        return User::query()->findOrFail($id);
    }

    protected function authorizeDeletion(Model $record): void
    {
        $this->authorize('deleteStaff', $record);
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
        return __('Administrator deleted.');
    }

    protected function clearListedRecords(): void
    {
        unset($this->administrators);
    }

    public function render(): View
    {
        return view('livewire.administrators.index');
    }
}
