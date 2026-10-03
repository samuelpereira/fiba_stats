<?php

test('returns a successful response', function () {
    $response = $this->get(route('home'));

    $response->assertOk()
        ->assertSee(config('app.name'), false)
        ->assertDontSee('laravel.com', false);
});
