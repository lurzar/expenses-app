<?php

use Inertia\Testing\AssertableInertia as Assert;

test('example', function () {
    $response = $this->get('/');

    $response->assertInertia(fn (Assert $page) => $page
        ->component('Landing/Index'));
});
