<?php

namespace App\Livewire\Clubs;

use App\Actions\UpdateClub;
use App\Concerns\ProfileValidationRules;
use App\Models\Club;
use Flux\Flux;
use Illuminate\Validation\Rules\Password;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Edit club')]
class Edit extends Component
{
    use ProfileValidationRules;

    #[Locked]
    public Club $club;

    public string $name = '';

    public string $accessName = '';

    public string $accessEmail = '';

    public string $password = '';

    public string $password_confirmation = '';

    public function mount(Club $club): void
    {
        $this->authorize('update', $club);

        $access = $club->access()->sole();

        $this->club = $club;
        $this->name = $club->name;
        $this->accessName = $access->name;
        $this->accessEmail = $access->email;
    }

    public function save(UpdateClub $updateClub): void
    {
        $this->authorize('update', $this->club);

        $access = $this->club->access()->sole();

        $rules = [
            'name' => $this->nameRules(),
            'accessName' => $this->nameRules(),
            'accessEmail' => $this->emailRules($access->id),
        ];

        if ($this->password !== '') {
            $rules['password'] = ['required', 'string', Password::default(), 'confirmed'];
        }

        $validated = $this->validate($rules);

        $updateClub->handle(
            $this->club,
            $validated['name'],
            $validated['accessName'],
            $validated['accessEmail'],
            $validated['password'] ?? null,
        );

        Flux::toast(variant: 'success', text: __('Club updated.'));

        $this->redirect(route('clubs.index'), navigate: true);
    }
}
