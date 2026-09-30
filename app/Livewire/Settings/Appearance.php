<?php

namespace App\Livewire\Settings;

use App\Livewire\Concerns\InteractsWithAuthenticatedUser;
use LaravelLang\Locales\Facades\Locales;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Appearance settings')]
class Appearance extends Component
{
    use InteractsWithAuthenticatedUser;

    public string $locale = '';

    public function mount(): void
    {
        $this->locale = $this->actor()->locale ?? app()->getLocale();
    }

    public function updatedLocale(string $locale): void
    {
        abort_unless(Locales::isInstalled($locale), 404);

        $user = $this->actor();
        $user->locale = $locale;
        $user->save();

        $this->redirect(route('appearance.edit'), navigate: true);
    }
}
