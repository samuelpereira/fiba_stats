<?php

use App\Livewire\Statistics\Record;
use App\Models\Game;
use App\Models\Player;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Features\SupportTesting\Testable;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind different classes or traits.
|
*/

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

function giveStartingFive(Game $game, ?Player $player = null): void
{
    if ($game->players()->wherePivot('starter', true)->count() === 5) {
        return;
    }

    $squad = collect();

    if ($player instanceof Player && $player->team_id === $game->team_id) {
        $squad->push($player);
    }

    while ($squad->count() < 5) {
        $squad->push(Player::factory()->create([
            'team_id' => $game->team_id,
        ]));
    }

    $game->players()->sync(
        $squad->mapWithKeys(fn (Player $squadPlayer): array => [
            $squadPlayer->id => ['starter' => true],
        ])->all(),
    );
}

function startCollectingPeriod(Testable $test): Testable
{
    return $test->call('recordQuarterStart');
}

function statisticsRecord(User $user, Game $game, bool $openPeriod = true): Testable
{
    $test = Livewire::actingAs($user)->test(Record::class, ['game' => $game]);

    if (! $openPeriod || $test->get('periodIsOpen')) {
        return $test;
    }

    if ($test->get('awaitingQuarterStart')) {
        return $test->call('startQuarter');
    }

    return startCollectingPeriod($test);
}
