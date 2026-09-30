<?php

namespace App\Livewire\Administrators;

use App\Actions\UpdateStaffMember;
use App\Concerns\ProfileValidationRules;
use App\Enums\RoleName;
use App\Livewire\Concerns\InteractsWithAuthenticatedUser;
use App\Models\User;
use Flux\Flux;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Edit administrator')]
class Edit extends Component
{
    use InteractsWithAuthenticatedUser, ProfileValidationRules;

    #[Locked]
    public User $user;

    public string $name = '';

    public string $email = '';

    public string $password = '';

    public string $password_confirmation = '';

    public string $role = '';

    public function mount(User $user): void
    {
        $this->authorize('updateStaff', $user);

        $assignedRole = $user->assignedRole();

        $this->user = $user;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->role = $assignedRole instanceof RoleName ? $assignedRole->value : RoleName::Administrator->value;
    }

    public function save(UpdateStaffMember $updateStaffMember): void
    {
        $this->authorize('updateStaff', $this->user);

        $rules = [
            'name' => $this->nameRules(),
            'email' => $this->emailRules($this->user->id),
        ];

        if ($this->password !== '') {
            $rules['password'] = ['required', 'string', Password::default(), 'confirmed'];
        }

        if ($this->showRoleField()) {
            $rules['role'] = ['required', Rule::enum(RoleName::class)->only(RoleName::staff())];
        }

        $validated = $this->validate($rules);

        $role = $this->showRoleField()
            ? RoleName::from($validated['role'])
            : RoleName::Administrator;

        $updateStaffMember->handle(
            $this->actor(),
            $this->user,
            $validated['name'],
            $validated['email'],
            $role,
            $validated['password'] ?? null,
        );

        Flux::toast(variant: 'success', text: __('Administrator updated.'));

        $this->redirect(route('administrators.index'), navigate: true);
    }

    #[Computed]
    public function showRoleField(): bool
    {
        return $this->actor()->isNot($this->user);
    }
}
