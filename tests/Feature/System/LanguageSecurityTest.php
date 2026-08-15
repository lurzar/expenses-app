<?php

test('unsupported locales are rejected', function () {
    $this->get('/language/fr')
        ->assertNotFound();
});

test('supported locales are stored in the session', function () {
    $this->from('/')
        ->get('/language/my')
        ->assertRedirect('/');

    $this->assertEquals('my', session('locale'));
});
