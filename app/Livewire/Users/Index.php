<?php

namespace App\Livewire\Users;

use App\Enums\PermissionName;
use App\Enums\RoleName;
use App\Livewire\Concerns\InteractsWithAuthenticatedUser;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Users')]
class Index extends Component
{
    use InteractsWithAuthenticatedUser;

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

    public function render(): View
    {
        return view('livewire.users.index');
    }
}
