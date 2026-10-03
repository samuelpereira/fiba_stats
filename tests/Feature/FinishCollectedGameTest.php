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
        ->assertSet('confirmingGameFinish', false)
        ->assertRedirect(route('statistics.index'));
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
        ->assertSet('confirmingGameFinish', true)
        ->call('finishGameAndExit')
        ->assertRedirect(route('statistics.index'));

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
        ->assertSet('confirmingGameFinish', true)
        ->call('leaveWithoutFinishing')
        ->assertRedirect(route('statistics.index'));

    expect($game->refresh()->status)->toBe(GameStatus::Scheduled);
});
