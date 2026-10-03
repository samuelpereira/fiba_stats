<?php

use App\Enums\HomeAway;
use App\Enums\StatType;
use App\Livewire\Statistics\Show as StatisticsShow;
use App\Models\Club;
use App\Models\Game;
use App\Models\GameEvent;
use App\Models\Player;
use App\Models\Team;
use App\Models\User;
use App\Support\ShotCourt;
use Database\Seeders\RolesAndPermissionsSeeder;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
});

test('the statistics button opens the game box score', function () {
    $club = Club::factory()->create();
    $team = Team::factory()->create([
        'club_id' => $club->id,
        'name' => 'ADC',
    ]);
    $member = User::factory()->memberOf($club)->withTeams($team)->create();
    $game = Game::factory()->create([
        'team_id' => $team->id,
        'opponent' => 'Benfica',
        'home_away' => HomeAway::Home,
    ]);
    $player = Player::factory()->create([
        'team_id' => $team->id,
        'name' => 'Joao Miguel',
        'number' => 7,
    ]);
    $bench = Player::factory()->create([
        'team_id' => $team->id,
        'name' => 'Pedro Silva',
        'number' => 12,
    ]);

    $game->players()->attach([
        $player->id => ['starter' => true],
        $bench->id => ['starter' => false],
    ]);

    GameEvent::factory()->create([
        'game_id' => $game->id,
        'player_id' => null,
        'home_away' => null,
        'type' => StatType::QuarterStarted,
        'quarter' => 1,
        'clock' => '10:00',
    ]);
    $made = ShotCourt::fromFeet(22.42, 8.05);
    $missed = ShotCourt::fromFeet(47.12, 4.99);

    GameEvent::factory()->create([
        'game_id' => $game->id,
        'player_id' => $player->id,
        'home_away' => HomeAway::Home,
        'type' => StatType::TwoPointMade,
        'quarter' => 1,
        'clock' => '09:00',
        'court_x' => $made['x'],
        'court_y' => $made['y'],
    ]);
    GameEvent::factory()->create([
        'game_id' => $game->id,
        'player_id' => $player->id,
        'home_away' => HomeAway::Home,
        'type' => StatType::ThreePointMissed,
        'quarter' => 1,
        'clock' => '08:30',
        'court_x' => $missed['x'],
        'court_y' => $missed['y'],
    ]);
    GameEvent::factory()->create([
        'game_id' => $game->id,
        'player_id' => null,
        'home_away' => HomeAway::Away,
        'type' => StatType::ThreePointMade,
        'quarter' => 1,
        'clock' => '08:00',
    ]);
    GameEvent::factory()->create([
        'game_id' => $game->id,
        'player_id' => $player->id,
        'home_away' => HomeAway::Home,
        'type' => StatType::Foul,
        'quarter' => 1,
        'clock' => '07:30',
    ]);
    GameEvent::factory()->create([
        'game_id' => $game->id,
        'player_id' => $player->id,
        'home_away' => HomeAway::Home,
        'type' => StatType::Foul,
        'quarter' => 1,
        'clock' => '07:00',
    ]);
    GameEvent::factory()->create([
        'game_id' => $game->id,
        'player_id' => null,
        'home_away' => HomeAway::Away,
        'type' => StatType::Foul,
        'quarter' => 1,
        'clock' => '06:30',
    ]);

    $this->actingAs($member)
        ->get(route('statistics.show', ['game' => $game, 'from' => 'dashboard']))
        ->assertOk()
        ->assertSee(route('dashboard'), false);

    $this->actingAs($member)
        ->get(route('statistics.show', ['game' => $game, 'from' => 'statistics']))
        ->assertSee(route('statistics.index'), false);

    $this->actingAs($member)
        ->get(route('statistics.show', ['game' => $game, 'from' => 'record']))
        ->assertSee(route('statistics.record', $game), false);

    Livewire::actingAs($member)
        ->test(StatisticsShow::class, ['game' => $game])
        ->assertSee('ADC')
        ->assertSee('Benfica')
        ->assertSee('Joao Miguel')
        ->assertSee('Pedro Silva')
        ->assertSee('On court')
        ->assertSee('Bench')
        ->assertSee('Q1')
        ->assertSee('T')
        ->assertSee('F')
        ->assertViewHas('homePeriodFouls', 2)
        ->assertViewHas('awayPeriodFouls', 1)
        ->assertViewHas('courtPlayers', fn (array $players): bool => count($players) === 1 && $players[0]['name'] === 'Joao Miguel')
        ->assertViewHas('benchPlayers', fn (array $players): bool => count($players) === 1 && $players[0]['name'] === 'Pedro Silva')
        ->assertSee('1-1')
        ->assertSee('Matchup')
        ->assertSee('Shot Charts')
        ->assertSee('Court locations for shot makes and misses')
        ->assertSee('Q1')
        ->assertViewHas('shotChartsByPeriod', fn (array $charts): bool => count($charts) === 2
            && $charts[0]['label'] === 'Q1'
            && count($charts[0]['home']) === 2
            && count($charts[0]['away']) === 0
            && $charts[0]['home'][0]['made'] === true
            && $charts[0]['home'][1]['made'] === false
            && $charts[1]['label'] === 'Totals'
            && count($charts[1]['home']) === 2
            && count($charts[1]['away']) === 0)
        ->assertSee('FGM-A')
        ->assertSet('backUrl', route('statistics.index'));
});

test('a club cannot open another clubs game statistics', function () {
    $game = Game::factory()->create();
    $account = User::factory()->clubAccessFor(Club::factory()->create())->create();

    $this->actingAs($account)->get(route('statistics.show', $game))->assertForbidden();
});
