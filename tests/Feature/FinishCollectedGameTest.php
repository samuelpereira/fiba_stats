<?php

use App\Enums\GameStatus;
use App\Enums\HomeAway;
use App\Enums\StatType;
use App\Livewire\Statistics\Record as StatisticsRecord;
use App\Models\Club;
use App\Models\Game;
use App\Models\Player;
use App\Models\Team;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Livewire\Livewire;

beforeEach(function () {
    $this->seed(RolesAndPermissionsSeeder::class);
});

test('exit before the fourth quarter ends returns to the statistics list', function () {
    $club = Club::factory()->create();
    $member = User::factory()->memberOf($club)->create();
    $game = Game::factory()->create([
        'team_id' => Team::factory()->create(['club_id' => $club->id])->id,
        'home_away' => HomeAway::Home,
    ]);
    $player = Player::factory()->create(['team_id' => $game->team_id]);

    giveStartingFive($game, $player);

    grantStatisticsTeamAccess($member, $game);

    Livewire::actingAs($member)
        ->test(StatisticsRecord::class, ['game' => $game])
        ->call('attemptExit')
        ->assertSet('confirmingExit', true)
        ->call('confirmExit')
        ->assertSet('confirmingGameFinish', false)
        ->assertRedirect(route('dashboard'));
});

test('exit after the fourth quarter ends can finish the game and save the score', function () {
    $club = Club::factory()->create();
    $member = User::factory()->memberOf($club)->create();
    $game = Game::factory()->create([
        'team_id' => Team::factory()->create(['club_id' => $club->id])->id,
        'home_away' => HomeAway::Home,
        'status' => GameStatus::Scheduled,
    ]);
    $player = Player::factory()->create(['team_id' => $game->team_id]);

    giveStartingFive($game, $player);

    statisticsRecord($member, $game)
        ->call('select', StatType::TwoPointMade->value)
        ->call('record', $player->id)
        ->set('quarter', 4)
        ->set('clock', '00:00')
        ->call('endQuarter')
        ->call('attemptExit')
        ->assertSet('confirmingExit', true)
        ->call('confirmExit')
        ->assertSet('confirmingGameFinish', true)
        ->call('finishGameAndExit')
        ->assertRedirect(route('dashboard'));

    $game->refresh();

    expect($game->status)->toBe(GameStatus::Played)
        ->and($game->home_points)->toBe(2)
        ->and($game->away_points)->toBe(0);
});

test('exit after the fourth quarter ends can leave without finishing the game', function () {
    $club = Club::factory()->create();
    $member = User::factory()->memberOf($club)->create();
    $game = Game::factory()->create([
        'team_id' => Team::factory()->create(['club_id' => $club->id])->id,
    ]);
    $player = Player::factory()->create(['team_id' => $game->team_id]);

    giveStartingFive($game, $player);

    grantStatisticsTeamAccess($member, $game);

    Livewire::actingAs($member)
        ->test(StatisticsRecord::class, ['game' => $game])
        ->set('quarter', 4)
        ->set('clock', '00:00')
        ->call('endQuarter')
        ->call('attemptExit')
        ->assertSet('confirmingExit', true)
        ->call('confirmExit')
        ->assertSet('confirmingGameFinish', true)
        ->call('leaveWithoutFinishing')
        ->assertRedirect(route('dashboard'));

    expect($game->refresh()->status)->toBe(GameStatus::Scheduled);
});

test('a finished game does not offer to start the next quarter', function () {
    $club = Club::factory()->create();
    $member = User::factory()->memberOf($club)->create();
    $game = Game::factory()->create([
        'team_id' => Team::factory()->create(['club_id' => $club->id])->id,
        'status' => GameStatus::Played,
    ]);

    giveStartingFive($game);

    $game->events()->create([
        'type' => StatType::QuarterEnded,
        'quarter' => 4,
        'clock' => '00:00',
    ]);

    grantStatisticsTeamAccess($member, $game);

    Livewire::actingAs($member)
        ->test(StatisticsRecord::class, ['game' => $game])
        ->assertSet('quarter', 5)
        ->assertSet('awaitingQuarterStart', true)
        ->assertDontSee('Start Q5')
        ->call('startQuarter')
        ->assertSet('quarter', 5)
        ->assertSet('awaitingQuarterStart', true);
});

test('played game scoreboard uses stored totals once for each team', function () {
    $club = Club::factory()->create();
    $member = User::factory()->memberOf($club)->create();
    $game = Game::factory()->create([
        'team_id' => Team::factory()->create(['club_id' => $club->id])->id,
        'home_away' => HomeAway::Home,
        'status' => GameStatus::Played,
        'home_points' => 67,
        'away_points' => 54,
    ]);
    $player = Player::factory()->create(['team_id' => $game->team_id]);

    giveStartingFive($game, $player);

    grantStatisticsTeamAccess($member, $game);

    $record = Livewire::actingAs($member)->test(StatisticsRecord::class, ['game' => $game]);

    expect($record->instance()->teamPoints())->toBe(67)
        ->and($record->instance()->opponentDisplayPoints())->toBe(54);

});
