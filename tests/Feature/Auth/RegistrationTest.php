<?php

test('registration screen is unavailable', function () {
    $this->get('/register')->assertNotFound();
});

test('new users cannot register', function () {
    $this->post('/register', [
        'name' => 'John Doe',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ])->assertNotFound();

    $this->assertGuest();
});
