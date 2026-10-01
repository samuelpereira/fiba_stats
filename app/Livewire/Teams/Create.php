<?php

namespace App\Livewire\Teams;

use App\Actions\CreateTeam as CreateTeamAction;
use App\Enums\Gender;
use App\Enums\PermissionName;
use App\Livewire\Concerns\InteractsWithAuthenticatedUser;
use App\Models\Club;
use App\Models\Season;
use App\Models\Team;
use Closure;
use Flux\Flux;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

#[Title('New team')]
class Create extends Component
{
    use InteractsWithAuthenticatedUser, WithFileUploads;

    public string $name = '';

    public string $clubId = '';

    public string $seasonId = '';

    public string $description = '';

    public string $competition = '';

    public string $ageGroup = '';

    public string $gender = '';

    public string $fpbUrl = '';

    public int $order = 0;

    public bool $enabled = true;

    public ?TemporaryUploadedFile $photo = null;

    public function mount(): void
    {
        $this->authorize('create', Team::class);
    }

    public function save(CreateTeamAction $createTeam): void
    {
        $actor = $this->actor();

        $this->authorize('create', Team::class);

        $validated = $this->validate($this->rules());

        $createTeam->handle(
            $actor,
            $validated['name'],
            (int) $validated['seasonId'],
            Gender::from($validated['gender']),
            $actor->can(PermissionName::CreateClubs->value) ? (int) $validated['clubId'] : null,
            $validated['description'] ?? null,
            $validated['competition'] ?? null,
            $validated['ageGroup'] ?? null,
            $validated['fpbUrl'] ?? null,
            (int) $validated['order'],
            (bool) $validated['enabled'],
            $this->photo,
        );

        Flux::toast(variant: 'success', text: __('Team created.'));

        $this->redirect(route('teams.index'), navigate: true);
    }

    /**
     * @return array<string, mixed>
     */
    private function rules(): array
    {
        $actor = $this->actor();
        $clubId = $actor->can(PermissionName::CreateClubs->value) ? $this->clubId : $actor->club_id;
        $slug = Team::slugFromName($this->name);

        $rules = [
            'name' => [
                'required',
                'string',
                'max:255',
                function (string $attribute, mixed $value, Closure $fail) use ($slug, $clubId): void {
                    if ($slug === '') {
                        $fail(__('The name must contain letters or numbers.'));

                        return;
                    }

                    $exists = Team::query()
                        ->where('club_id', $clubId)
                        ->where('season_id', $this->seasonId)
                        ->where('slug', $slug)
                        ->exists();

                    if ($exists) {
                        $fail(__('A team with this name already exists for this club and season.'));
                    }
                },
            ],
            'seasonId' => ['required', 'integer', Rule::exists(Season::class, 'id')],
            'description' => ['nullable', 'string'],
            'competition' => ['nullable', 'string', 'max:255'],
            'ageGroup' => ['nullable', 'string', 'max:255'],
            'gender' => ['required', Rule::enum(Gender::class)],
            'fpbUrl' => ['nullable', 'url', 'max:255'],
            'order' => ['required', 'integer', 'min:0'],
            'enabled' => ['boolean'],
            'photo' => ['nullable', 'image', 'max:2048'],
        ];

        if ($actor->can(PermissionName::CreateClubs->value)) {
            $rules['clubId'] = ['required', 'integer', Rule::exists(Club::class, 'id')];
        }

        return $rules;
    }

    /**
     * @return Collection<int, Club>
     */
    #[Computed]
    public function clubs(): Collection
    {
        return Club::query()->orderBy('name')->get();
    }

    /**
     * @return Collection<int, Season>
     */
    #[Computed]
    public function seasons(): Collection
    {
        return Season::query()->orderByDesc('starts_at')->get();
    }
}
