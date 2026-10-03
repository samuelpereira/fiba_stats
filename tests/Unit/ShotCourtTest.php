<?php

use App\Enums\StatType;
use App\Support\ShotCourt;

test('two point shots must be inside the three point arc', function () {
    expect(ShotCourt::allows(StatType::TwoPointMade, 500, 300))->toBeTrue()
        ->and(ShotCourt::allows(StatType::TwoPointMade, 500, 520))->toBeFalse()
        ->and(ShotCourt::allows(StatType::TwoPointMade, 110, ShotCourt::BaselineY))->toBeFalse()
        ->and(ShotCourt::allows(StatType::TwoPointMissed, 850, 700))->toBeFalse();
});

test('three point shots must be outside the three point arc', function () {
    expect(ShotCourt::allows(StatType::ThreePointMade, 500, 520))->toBeTrue()
        ->and(ShotCourt::allows(StatType::ThreePointMade, 500, 300))->toBeFalse()
        ->and(ShotCourt::allows(StatType::ThreePointMissed, 150, 400))->toBeTrue();
});

test('three point shots can be marked in the baseline corners', function () {
    expect(ShotCourt::allows(StatType::ThreePointMade, 110, ShotCourt::BaselineY))->toBeTrue()
        ->and(ShotCourt::allows(StatType::ThreePointMade, ShotCourt::Width - 110, ShotCourt::BaselineY))->toBeTrue();
});

test('a half-court position in feet maps onto the drawn court', function () {
    expect(ShotCourt::fromFeet(22.42, 8.05))->toBe(['x' => 459, 'y' => 189])
        ->and(ShotCourt::fromFeet(47.12, 4.99))->toBe(['x' => 854, 'y' => 140])
        ->and(ShotCourt::fromFeet(0, 0))->toBe(['x' => 100, 'y' => 60])
        ->and(ShotCourt::fromFeet(50, 47))->toBe(['x' => 900, 'y' => 812]);
});

test('court clicks must stay inside the court bounds', function () {
    expect(ShotCourt::allows(StatType::TwoPointMade, 20, 300))->toBeFalse()
        ->and(ShotCourt::allows(StatType::TwoPointMade, 500, 20))->toBeFalse();
});

test('the three point line follows the hoop-centred arc', function () {
    $join = ShotCourt::threePointJoinY();
    $radius = ShotCourt::threePointRadius();

    expect($join)->toBeGreaterThan(ShotCourt::hoopY())
        ->and($join)->toBeLessThan(ShotCourt::HalfCourtY);

    $distance = hypot(ShotCourt::cornerX() - ShotCourt::HoopX, $join - ShotCourt::hoopY());

    expect($distance)->toEqualWithDelta($radius, 1);
});
