<?php

namespace App\Livewire\Administrators;

use App\Actions\CreateStaffMember;
use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Enums\RoleName;
use App\Livewire\Concerns\InteractsWithAuthenticatedUser;
use App\Models\User;
use Flux\Flux;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('New administrator')]
class Create extends Component
{
    use InteractsWithAuthenticatedUser, PasswordValidationRules, ProfileValidationRules;

    public string $name = '';

    public string $email = '';

    public string $password = '';

    public string $password_confirmation = '';

    public string $role = RoleName::Administrator->value;

    public function mount(): void
    {
        $this->authorize('createStaff', User::class);
    }

    public function save(CreateStaffMember $createStaffMember): void
    {
        $this->authorize('createStaff', User::class);

        $validated = $this->validate([
            'name' => $this->nameRules(),
            'email' => $this->emailRules(),
            'password' => $this->passwordRules(),
            'role' => ['required', Rule::enum(RoleName::class)->only(RoleName::staff())],
        ]);

        $createStaffMember->handle(
            $this->actor(),
            $validated['name'],
            $validated['email'],
            $validated['password'],
            RoleName::from($validated['role']),
        );

        Flux::toast(variant: 'success', text: __('Administrator created.'));

        $this->redirect(route('administrators.index'), navigate: true);
    }
}
