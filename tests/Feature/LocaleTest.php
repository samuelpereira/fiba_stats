<?php

use App\Livewire\Settings\Appearance;
use App\Models\User;
use Livewire\Livewire;

test('an authenticated user switches the language from appearance', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    Livewire::test(Appearance::class)
        ->set('locale', 'pt')
        ->assertHasNoErrors()
        ->assertRedirect(route('appearance.edit'));

    expect($user->refresh()->locale)->toBe('pt');

    $this->flushSession();

    $this->actingAs($user)
        ->get(route('appearance.edit'))
        ->assertSee('Idioma')
        ->assertSee('Segurança')
        ->assertSee('Português')
        ->assertSee('English');
});

test('an unknown locale is not installed', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    Livewire::test(Appearance::class)
        ->set('locale', 'de')
        ->assertNotFound();

    expect($user->refresh()->locale)->toBeNull();
});
