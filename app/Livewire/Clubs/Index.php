<?php

namespace App\Livewire\Clubs;

use App\Actions\DeleteClub;
use App\Livewire\Concerns\ConfirmsDeletion;
use App\Models\Club;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Clubs')]
class Index extends Component
{
    use ConfirmsDeletion;

    public function mount(): void
    {
        $this->authorize('viewAny', Club::class);
    }

    /**
     * @return Collection<int, Club>
     */
    #[Computed]
    public function clubs(): Collection
    {
        return Club::query()->with('access')->orderBy('name')->get();
    }

    protected function deletionModalName(): string
    {
        return 'confirm-delete';
    }

    protected function recordForDeletion(int|string $key): Model
    {
        return Club::query()->findOrFail($key);
    }

    protected function performDeletion(Model $record): void
    {
        if (! $record instanceof Club) {
            return;
        }

        (new DeleteClub)->handle($record);
    }

    protected function deletedMessage(): string
    {
        return __('Club deleted.');
    }

    protected function clearListedRecords(): void
    {
        unset($this->clubs);
    }

    public function render(): View
    {
        return view('livewire.clubs.index');
    }
}
