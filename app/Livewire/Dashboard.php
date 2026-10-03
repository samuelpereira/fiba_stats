<?php

namespace App\Livewire;

use App\Enums\GameStatus;
use App\Enums\PermissionName;
use App\Livewire\Concerns\InteractsWithAuthenticatedUser;
use App\Models\Club;
use App\Models\Game;
use App\Models\Player;
use App\Models\Season;
use App\Models\Team;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.app')]
#[Title('Dashboard')]
class Dashboard extends Component
{
    use InteractsWithAuthenticatedUser;

    #[Computed]
    public function currentSeason(): ?Season
    {
        return Season::query()->where('is_current', true)->first();
    }

    /**
     * @return list<array{label: string, value: int|string, href: string|null}>
     */
    #[Computed]
    public function summaryCards(): array
    {
        $actor = $this->actor();
        $cards = [];

        if ($actor->can('viewAny', Club::class)) {
            $cards[] = [
                'label' => __('Clubs'),
                'value' => Club::query()->count(),
                'href' => route('clubs.index'),
            ];
        }

        if ($actor->can('viewAny', Season::class)) {
            $cards[] = [
                'label' => __('Seasons'),
                'value' => Season::query()->count(),
                'href' => route('seasons.index'),
            ];
        }

        if ($actor->can('viewAny', Team::class)) {
            $cards[] = [
                'label' => __('Teams'),
                'value' => $this->teamsQuery()->count(),
                'href' => route('teams.index'),
            ];
        }

        if ($actor->can('viewAny', Player::class)) {
            $cards[] = [
                'label' => __('Players'),
                'value' => $this->playersQuery()->count(),
                'href' => route('players.index'),
            ];
        }

        if ($actor->can('viewAny', Game::class)) {
            $games = $this->gamesQuery();

            $cards[] = [
                'label' => __('Games'),
                'value' => (clone $games)->count(),
                'href' => route('games.index'),
            ];

            $cards[] = [
                'label' => __('Scheduled'),
                'value' => (clone $games)->where('status', GameStatus::Scheduled)->count(),
                'href' => route('games.index'),
            ];

            $cards[] = [
                'label' => __('Played'),
                'value' => (clone $games)->where('status', GameStatus::Played)->count(),
                'href' => route('games.index'),
            ];
        }

        if ($actor->can('viewAny', User::class) && $actor->can(PermissionName::CreateClubs->value)) {
            $cards[] = [
                'label' => __('Users'),
                'value' => User::query()->whereNotNull('club_id')->count(),
                'href' => route('users.index'),
            ];
        }

        return $cards;
    }

    /**
     * @return Collection<int, Game>
     */
    #[Computed]
    public function upcomingGames(): Collection
    {
        if (! Gate::check('viewAny', Game::class) && ! Gate::check('collectAny', Game::class)) {
            return new Collection;
        }

        return $this->gamesQuery()
            ->where('status', GameStatus::Scheduled)
            ->where('date_time', '>=', now())
            ->orderBy('date_time')
            ->limit(5)
            ->get();
    }

    /**
     * @return Collection<int, Game>
     */
    #[Computed]
    public function recentGames(): Collection
    {
        if (! Gate::check('viewAny', Game::class) && ! Gate::check('collectAny', Game::class)) {
            return new Collection;
        }

        return $this->gamesQuery()
            ->where('date_time', '<', now())
            ->orderByDesc('date_time')
            ->limit(5)
            ->get();
    }

    public function canCollectStatistics(): bool
    {
        return Gate::check('collectAny', Game::class);
    }

    public function render(): View
    {
        return view('livewire.dashboard');
    }

    /**
     * @return Builder<Team>
     */
    private function teamsQuery(): Builder
    {
        $query = Team::query();

        $currentSeason = $this->currentSeason();

        if ($currentSeason !== null) {
            $query->where('season_id', $currentSeason->id);
        }

        if (! $this->actor()->can(PermissionName::CreateClubs->value)) {
            $query->where('club_id', $this->actor()->club_id);
        }

        return $query;
    }

    /**
     * @return Builder<Player>
     */
    private function playersQuery(): Builder
    {
        $query = Player::query();

        $currentSeason = $this->currentSeason();

        if ($currentSeason !== null) {
            $query->whereHas('team', fn (Builder $teamQuery) => $teamQuery->where('season_id', $currentSeason->id));
        }

        if (! $this->actor()->can(PermissionName::CreateClubs->value)) {
            $query->whereHas('team', fn (Builder $teamQuery) => $teamQuery->where('club_id', $this->actor()->club_id));
        }

        return $query;
    }

    /**
     * @return Builder<Game>
     */
    private function gamesQuery(): Builder
    {
        $query = Game::query()->with(['team.season', 'team.club']);

        $currentSeason = $this->currentSeason();

        if ($currentSeason !== null) {
            $query->whereHas('team', fn (Builder $teamQuery) => $teamQuery->where('season_id', $currentSeason->id));
        }

        if (! $this->actor()->can(PermissionName::CreateClubs->value)) {
            $query->forStatisticsCollector($this->actor());
        }

        return $query;
    }
}
