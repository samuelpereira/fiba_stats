<span class="pointer-events-none relative flex size-10 shrink-0 items-center justify-center overflow-hidden rounded-full border border-zinc-900 bg-white sm:size-14">
    @if ($this->game->opponent_photo_url)
        <img src="{{ $this->game->opponent_photo_url }}" alt="" class="absolute inset-0 size-full object-cover" />
    @else
        <flux:icon name="user-group" class="size-5 sm:size-7" />
    @endif
</span>
