<?php

namespace App\Livewire\Clubs;

use App\Actions\CreateClub;
use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Models\Club;
use Flux\Flux;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('New club')]
class Create extends Component
{
    use PasswordValidationRules, ProfileValidationRules;

    public string $name = '';

    public string $accessName = '';

    public string $accessEmail = '';

    public string $password = '';

    public string $password_confirmation = '';

    public function mount(): void
    {
        $this->authorize('create', Club::class);
    }

    public function save(CreateClub $createClub): void
    {
        $this->authorize('create', Club::class);

        $validated = $this->validate([
            'name' => $this->nameRules(),
            'accessName' => $this->nameRules(),
            'accessEmail' => $this->emailRules(),
            'password' => $this->passwordRules(),
        ]);

        $createClub->handle(
            $validated['name'],
            $validated['accessName'],
            $validated['accessEmail'],
            $validated['password'],
        );

        Flux::toast(variant: 'success', text: __('Club created.'));

        $this->redirect(route('clubs.index'), navigate: true);
    }
}
