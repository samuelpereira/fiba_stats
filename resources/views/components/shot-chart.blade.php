@props([
    /** @var list<array{id: int, x: int, y: int, made: bool}> */
    'shots' => [],
])

<svg
    viewBox="0 0 {{ \App\Support\ShotCourt::Width }} {{ \App\Support\ShotCourt::Height }}"
    xmlns="http://www.w3.org/2000/svg"
    class="mx-auto block w-full"
    role="img"
    aria-label="{{ __('Shot chart') }}"
    {{ $attributes }}
>
    <rect width="{{ \App\Support\ShotCourt::Width }}" height="{{ \App\Support\ShotCourt::Height }}" fill="#ffffff" />
    <x-half-court-markings stroke="#4a4867" three-point-stroke="#4a4867" />
    @foreach ($shots as $shot)
        @if ($shot['made'])
            <circle
                cx="{{ $shot['x'] }}"
                cy="{{ $shot['y'] }}"
                r="10"
                fill="#16a34a"
            />
        @else
            <g stroke="#dc2626" stroke-width="3.5" stroke-linecap="round">
                <line x1="{{ $shot['x'] - 6 }}" y1="{{ $shot['y'] - 6 }}" x2="{{ $shot['x'] + 6 }}" y2="{{ $shot['y'] + 6 }}" />
                <line x1="{{ $shot['x'] + 6 }}" y1="{{ $shot['y'] - 6 }}" x2="{{ $shot['x'] - 6 }}" y2="{{ $shot['y'] + 6 }}" />
            </g>
        @endif
    @endforeach
</svg>
