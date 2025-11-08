<?php

test('registration screen can be rendered', function () {
    $response = $this->get('/register');

    $response->assertStatus(200);
});

test('new users can register', function () {
    $response = $this->post('/register', [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
        'role' => 'buyer',
    ]);

    $response->assertSessionHasNoErrors();
    expect(\App\Models\User::where('email', 'test@example.com')->exists())->toBeTrue();
    expect(auth()->user())->not->toBeNull();
    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard', absolute: false));
});
