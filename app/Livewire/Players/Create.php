<?php

namespace App\Livewire\Players;

use App\Actions\CreatePlayer as CreatePlayerAction;
use App\Enums\PermissionName;
use App\Livewire\Concerns\InteractsWithAuthenticatedUser;
use App\Models\Player;
use App\Models\Team;
use Carbon\CarbonImmutable;
use Closure;
use Flux\Flux;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

#[Title('New player')]
class Create extends Component
{
    use InteractsWithAuthenticatedUser, WithFileUploads;

    public string $name = '';

    public string $teamId = '';

    public string $fpbId = '';

    public string $dateOfBirth = '';

    public string $number = '';

    public string $position = '';

    public int $order = 0;

    public bool $enabled = true;

    public ?TemporaryUploadedFile $photo = null;

    public function mount(): void
    {
        $this->authorize('create', Player::class);
    }

    public function save(CreatePlayerAction $createPlayer): void
    {
        $this->authorize('create', Player::class);

        $validated = $this->validate($this->rules());

        $createPlayer->handle(
            $this->actor(),
            $validated['name'],
            (int) $validated['teamId'],
            $validated['fpbId'] ?? null,
            $this->dateOfBirth($validated['dateOfBirth'] ?? null),
            $this->shirtNumber($validated['number'] ?? null),
            $validated['position'] ?? null,
            (int) $validated['order'],
            (bool) $validated['enabled'],
            $this->photo,
        );

        Flux::toast(variant: 'success', text: __('Player created.'));

        $this->redirect(route('players.index'), navigate: true);
    }

    /**
     * @return array<string, mixed>
     */
    private function rules(): array
    {
        $slug = Player::slugFromName($this->name);

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                function (string $attribute, mixed $value, Closure $fail) use ($slug): void {
                    if ($slug === '') {
                        $fail(__('The name must contain letters or numbers.'));

                        return;
                    }

                    $exists = Player::query()
                        ->where('team_id', $this->teamId)
                        ->where('slug', $slug)
                        ->exists();

                    if ($exists) {
                        $fail(__('A player with this name already exists on this team.'));
                    }
                },
            ],
            'teamId' => ['required', 'integer', Rule::exists(Team::class, 'id')],
            'fpbId' => ['nullable', 'string', 'max:255'],
            'dateOfBirth' => ['nullable', 'date', 'before:today'],
            'number' => [
                'nullable',
                'integer',
                'between:0,99',
                Rule::unique(Player::class, 'number')->where(fn ($query) => $query->where('team_id', $this->teamId)),
            ],
            'position' => ['nullable', 'string', 'max:255'],
            'order' => ['required', 'integer', 'min:0'],
            'enabled' => ['boolean'],
            'photo' => ['nullable', 'image', 'max:2048'],
        ];
    }

    private function dateOfBirth(mixed $value): ?CarbonImmutable
    {
        if (! is_string($value) || $value === '') {
            return null;
        }

        return CarbonImmutable::parse($value)->startOfDay();
    }

    private function shirtNumber(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (int) $value;
    }

    /**
     * @return Collection<int, Team>
     */
    #[Computed]
    public function teams(): Collection
    {
        $actor = $this->actor();

        $query = Team::query()->with(['club', 'season'])->orderBy('name');

        if (! $actor->can(PermissionName::CreateClubs->value)) {
            $query->where('club_id', $actor->club_id);
        }

        return $query->get();
    }
}
