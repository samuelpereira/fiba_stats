<div
    data-shot-court
    x-data="{
        mark(event) {
            const svg = this.$refs.court;

            if (! svg) {
                return;
            }

            const point = svg.createSVGPoint();
            point.x = event.clientX;
            point.y = event.clientY;
            const location = point.matrixTransform(svg.getScreenCTM().inverse());

            $wire.recordShotLocation(Math.round(location.x), Math.round(location.y));
        },
    }"
    class="relative w-full"
>
    <svg
        x-ref="court"
        viewBox="0 0 {{ \App\Support\ShotCourt::Width }} {{ \App\Support\ShotCourt::Height }}"
        xmlns="http://www.w3.org/2000/svg"
        class="mx-auto block w-full max-w-xl cursor-crosshair touch-none select-none"
        role="img"
        aria-label="{{ __('Half court') }}"
        @click="mark($event)"
    >
        <rect width="{{ \App\Support\ShotCourt::Width }}" height="{{ \App\Support\ShotCourt::Height }}" fill="#ffffff" />
        <x-half-court-markings stroke="#4a4867" three-point-stroke="#4a4867" />
    </svg>
</div>
