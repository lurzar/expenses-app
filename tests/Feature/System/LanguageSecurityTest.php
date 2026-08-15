<?php

use Illuminate\Routing\Exceptions\UrlGenerationException;

test('unsupported locales are rejected', function () {
    $this->get('/language/fr')
        ->assertNotFound();
});

test('the named route requires the live language parameter', function () {
    expect(route('language', ['language' => 'en'], false))->toBe('/language/en')
        ->and(route('language', ['language' => 'my'], false))->toBe('/language/my')
        ->and(fn () => route('language'))->toThrow(UrlGenerationException::class);
});

test('supported locales are stored in the session', function () {
    $this->from('/')
        ->get('/language/my')
        ->assertRedirect('/');

    $this->assertEquals('my', session('locale'));
});
