@props([
    'stroke' => '#ffffff',
    'threePointStroke' => null,
])

@php
    use App\Support\ShotCourt;

    $arcStroke = $threePointStroke ?? $stroke;
    $threePointPath = ShotCourt::threePointLinePath();
    $hoopY = ShotCourt::hoopY();
    $paintWidth = ShotCourt::pixels(16.4);
    $paintDepth = ShotCourt::pixels(19.47);
    $paintLeft = ShotCourt::HoopX - (int) round($paintWidth / 2);
    $freeThrowY = ShotCourt::BaselineY + $paintDepth;
    $freeThrowRadius = ShotCourt::pixels(6.14);
    $restrictedRadius = ShotCourt::pixels(4);
    $centerCircleRadius = ShotCourt::pixels(6.14);
    $backboardY = ShotCourt::BaselineY + ShotCourt::pixels(4.03);
    $backboardHalf = (int) round(ShotCourt::pixels(5.57) / 2);
@endphp

<rect
    x="{{ ShotCourt::CourtLeft }}"
    y="{{ ShotCourt::BaselineY }}"
    width="{{ ShotCourt::CourtRight - ShotCourt::CourtLeft }}"
    height="{{ ShotCourt::HalfCourtY - ShotCourt::BaselineY }}"
    fill="none"
    stroke="{{ $stroke }}"
    stroke-width="2"
/>
<line
    x1="{{ ShotCourt::CourtLeft }}"
    y1="{{ ShotCourt::HalfCourtY }}"
    x2="{{ ShotCourt::CourtRight }}"
    y2="{{ ShotCourt::HalfCourtY }}"
    stroke="{{ $stroke }}"
    stroke-width="2"
/>
<path
    d="M {{ ShotCourt::HoopX - $centerCircleRadius }} {{ ShotCourt::HalfCourtY }} A {{ $centerCircleRadius }} {{ $centerCircleRadius }} 0 0 1 {{ ShotCourt::HoopX + $centerCircleRadius }} {{ ShotCourt::HalfCourtY }}"
    fill="none"
    stroke="{{ $stroke }}"
    stroke-width="2"
/>
<line
    x1="{{ ShotCourt::HoopX - $backboardHalf }}"
    y1="{{ $backboardY }}"
    x2="{{ ShotCourt::HoopX + $backboardHalf }}"
    y2="{{ $backboardY }}"
    stroke="{{ $stroke }}"
    stroke-width="2"
    stroke-linecap="round"
/>
<circle cx="{{ ShotCourt::HoopX }}" cy="{{ $hoopY }}" r="12" fill="none" stroke="{{ $stroke }}" stroke-width="2" />
<path
    d="M {{ ShotCourt::HoopX - $restrictedRadius }} {{ $hoopY }} A {{ $restrictedRadius }} {{ $restrictedRadius }} 0 0 0 {{ ShotCourt::HoopX + $restrictedRadius }} {{ $hoopY }}"
    fill="none"
    stroke="{{ $stroke }}"
    stroke-width="2"
/>
<rect
    x="{{ $paintLeft }}"
    y="{{ ShotCourt::BaselineY }}"
    width="{{ $paintWidth }}"
    height="{{ $paintDepth }}"
    fill="none"
    stroke="{{ $stroke }}"
    stroke-width="2"
/>
<circle
    cx="{{ ShotCourt::HoopX }}"
    cy="{{ $freeThrowY }}"
    r="{{ $freeThrowRadius }}"
    fill="none"
    stroke="{{ $stroke }}"
    stroke-width="2"
/>
<path
    d="{{ $threePointPath }}"
    fill="none"
    stroke="{{ $arcStroke }}"
    stroke-width="2"
    stroke-linejoin="round"
    stroke-linecap="round"
/>
