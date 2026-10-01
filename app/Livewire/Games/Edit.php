<?php

namespace App\Livewire\Games;

use App\Actions\UpdateGame as UpdateGameAction;
use App\Enums\GameStatus;
use App\Enums\HomeAway;
use App\Enums\PermissionName;
use App\Livewire\Concerns\InteractsWithAuthenticatedUser;
use App\Models\Game;
use App\Models\Team;
use Carbon\CarbonImmutable;
use Flux\Flux;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Edit game')]
class Edit extends Component
{
    use InteractsWithAuthenticatedUser;

    #[Locked]
    public Game $game;

    public string $teamId = '';

    public string $fpbId = '';

    public string $opponent = '';

    public string $dateTime = '';

    public string $place = '';

    public string $homeAway = '';

    public string $competition = '';

    public string $journey = '';

    public string $homePoints = '';

    public string $awayPoints = '';

    public string $status = '';

    public function mount(Game $game): void
    {
        $this->authorize('update', $game);

        $this->game = $game;
        $this->teamId = (string) $game->team_id;
        $this->fpbId = $game->fpb_id ?? '';
        $this->opponent = $game->opponent;
        $this->dateTime = $game->date_time->format('Y-m-d\TH:i');
        $this->place = $game->place ?? '';
        $this->homeAway = $game->home_away->value;
        $this->competition = $game->competition ?? '';
        $this->journey = $game->journey ?? '';
        $this->homePoints = $game->home_points === null ? '' : (string) $game->home_points;
        $this->awayPoints = $game->away_points === null ? '' : (string) $game->away_points;
        $this->status = $game->status->value;
    }

    public function save(UpdateGameAction $updateGame): void
    {
        $this->authorize('update', $this->game);

        $validated = $this->validate($this->rules());

        $updateGame->handle(
            $this->actor(),
            $this->game,
            (int) $validated['teamId'],
            $validated['fpbId'] ?? null,
            $validated['opponent'],
            CarbonImmutable::parse($validated['dateTime']),
            $validated['place'] ?? null,
            HomeAway::from($validated['homeAway']),
            $validated['competition'] ?? null,
            $validated['journey'] ?? null,
            $this->points($validated['homePoints'] ?? null),
            $this->points($validated['awayPoints'] ?? null),
            GameStatus::from($validated['status']),
        );

        Flux::toast(variant: 'success', text: __('Game updated.'));

        $this->redirect(route('games.index'), navigate: true);
    }

    /**
     * @return array<string, mixed>
     */
    private function rules(): array
    {
        return [
            'teamId' => ['required', 'integer', Rule::exists(Team::class, 'id')],
            'fpbId' => ['nullable', 'string', 'max:255', Rule::unique(Game::class, 'fpb_id')->ignore($this->game->id)],
            'opponent' => ['required', 'string', 'max:255'],
            'dateTime' => ['required', 'date'],
            'place' => ['nullable', 'string', 'max:255'],
            'homeAway' => ['required', Rule::enum(HomeAway::class)],
            'competition' => ['nullable', 'string', 'max:255'],
            'journey' => ['nullable', 'string', 'max:255'],
            'homePoints' => ['nullable', 'integer', 'min:0', 'max:999'],
            'awayPoints' => ['nullable', 'integer', 'min:0', 'max:999'],
            'status' => ['required', Rule::enum(GameStatus::class)],
        ];
    }

    private function points(mixed $value): ?int
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
