<?php

namespace App\Livewire\Users;

use App\Actions\CreateManagedUser;
use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Enums\PermissionName;
use App\Livewire\Concerns\InteractsWithAuthenticatedUser;
use App\Livewire\Concerns\ManagesUserTeamAssignments;
use App\Models\Club;
use App\Models\User;
use Flux\Flux;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('New user')]
class Create extends Component
{
    use InteractsWithAuthenticatedUser, ManagesUserTeamAssignments, PasswordValidationRules, ProfileValidationRules;

    public string $name = '';

    public string $email = '';

    public string $password = '';

    public string $password_confirmation = '';

    public string $clubId = '';

    public function mount(): void
    {
        $this->authorize('create', User::class);
    }

    public function save(CreateManagedUser $createManagedUser): void
    {
        $actor = $this->actor();

        $this->authorize('create', User::class);

        $rules = [
            'name' => $this->nameRules(),
            'email' => $this->emailRules(),
            'password' => $this->passwordRules(),
        ];

        if ($actor->can(PermissionName::CreateClubs->value)) {
            $rules['clubId'] = ['required', 'integer', Rule::exists(Club::class, 'id')];
        }

        $rules = array_merge($rules, $this->teamAssignmentRules());

        $validated = $this->validate($rules);

        $createManagedUser->handle(
            $actor,
            $validated['name'],
            $validated['email'],
            $validated['password'],
            $actor->can(PermissionName::CreateClubs->value) ? (int) $validated['clubId'] : null,
            $validated['teamIds'],
        );

        Flux::toast(variant: 'success', text: __('User created.'));

        $this->redirect(route('users.index'), navigate: true);
    }

    /**
     * @return Collection<int, Club>
     */
    #[Computed]
    public function clubs(): Collection
    {
        return Club::query()->orderBy('name')->get();
    }
}
