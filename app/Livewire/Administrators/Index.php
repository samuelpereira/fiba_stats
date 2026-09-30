<?php

namespace App\Livewire\Administrators;

use App\Enums\RoleName;
use App\Livewire\Concerns\InteractsWithAuthenticatedUser;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Administrators')]
class Index extends Component
{
    use InteractsWithAuthenticatedUser;

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

    public function render(): View
    {
        return view('livewire.administrators.index');
    }
}
