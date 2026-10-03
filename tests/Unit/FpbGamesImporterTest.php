<?php

use App\Services\Fpb\FpbGamesImporter;

test('fpb parse extracts fixtures from team page html', function () {
    $html = <<<'HTML'
<h3 class="date">15 OUT 2025</h3>
<a href="/ficha-de-jogo?internalID=99901">
<span class="fullName">CA Gondomar</span>
<span class="fullName">FC Porto</span>
<div class="results_text">72</div>
<div class="results_text">68</div>
<div class="hour"><h3>20H30</h3></div>
<div class="location-wrapper"><b>Gondomar, Pavilhão</b></div>
<span class="competition"><span>1ª Divisão | Jornada 3</span></span>
HTML;

    $fixtures = app(FpbGamesImporter::class)->parse($html);

    expect($fixtures)->toHaveCount(1)
        ->and($fixtures[0]['id'])->toBe('99901')
        ->and($fixtures[0]['home'])->toBe('CA Gondomar')
        ->and($fixtures[0]['away'])->toBe('FC Porto')
        ->and($fixtures[0]['pc'])->toBe(72)
        ->and($fixtures[0]['pf'])->toBe(68)
        ->and($fixtures[0]['loc'])->toBe('Gondomar, Pavilhão')
        ->and($fixtures[0]['comp'])->toBe('Jornada 3')
        ->and($fixtures[0]['date_time']->format('Y-m-d H:i'))->toBe('2025-10-15 20:30');
});

test('fpb parse players extracts roster from team page html', function () {
    $html = <<<'HTML'
<div class="roster__player">
<a href="/atletas/4242"></a>
<h2 class="header__heading">Miguel Costa</h2>
<div class="header_position">Base</div>
<div class="header_jnumber">7</div>
</div>
HTML;

    $athletes = app(FpbGamesImporter::class)->parsePlayers($html);

    expect($athletes)->toHaveCount(1)
        ->and($athletes[0]['fpb_id'])->toBe('4242')
        ->and($athletes[0]['name'])->toBe('Miguel Costa')
        ->and($athletes[0]['position'])->toBe('Base')
        ->and($athletes[0]['number'])->toBe('7');
});
