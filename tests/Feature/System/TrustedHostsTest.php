<?php

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Middleware\TrustHosts;

test('the global middleware stack enforces trusted hosts', function () {
    expect(app(Kernel::class)->getGlobalMiddleware())
        ->toContain(TrustHosts::class);
});
