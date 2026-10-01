<?php

namespace App\Livewire\Users;

use App\Actions\UpdateManagedUser;
use App\Concerns\ProfileValidationRules;
use App\Enums\PermissionName;
use App\Livewire\Concerns\InteractsWithAuthenticatedUser;
use App\Models\Club;
use App\Models\User;
use Flux\Flux;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Edit user')]
class Edit extends Component
{
    use InteractsWithAuthenticatedUser, ProfileValidationRules;

    #[Locked]
    public User $user;

    public string $name = '';

    public string $email = '';

    public string $password = '';

    public string $password_confirmation = '';

    public string $clubId = '';

    public bool $enabled = true;

    public function mount(User $user): void
    {
        $this->authorize('update', $user);

        $this->user = $user;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->clubId = (string) $user->club_id;
        $this->enabled = $user->enabled;
    }

    public function save(UpdateManagedUser $updateManagedUser): void
    {
        $actor = $this->actor();

        $this->authorize('update', $this->user);

        $rules = [
            'name' => $this->nameRules(),
            'email' => $this->emailRules($this->user->id),
            'enabled' => ['boolean'],
        ];

        if ($this->password !== '') {
            $rules['password'] = ['required', 'string', Password::default(), 'confirmed'];
        }

        if ($actor->can(PermissionName::CreateClubs->value)) {
            $rules['clubId'] = ['required', 'integer', Rule::exists(Club::class, 'id')];
        }

        $validated = $this->validate($rules);

        $updateManagedUser->handle(
            $actor,
            $this->user,
            $validated['name'],
            $validated['email'],
            (bool) $validated['enabled'],
            $validated['password'] ?? null,
            $actor->can(PermissionName::CreateClubs->value) ? (int) $validated['clubId'] : null,
        );

        Flux::toast(variant: 'success', text: __('User updated.'));

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
