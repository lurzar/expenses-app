<?php

test('the financial shell stylesheet contains the approved semantic and accessibility contract', function () {
    $stylesheet = file_get_contents(dirname(__DIR__, 3).'/resources/css/app.css');

    expect($stylesheet)->toBeString()
        ->toContain('--app-canvas: #F5FAF8')
        ->toContain('--app-canvas: #091413')
        ->toContain('--app-brand-strong: #285A48')
        ->toContain('--app-brand-strong: #B0E4CC')
        ->toContain('*:focus-visible')
        ->toContain('min-height: 2.75rem')
        ->toContain('.app-language-link { display: inline-flex; min-width: 2.75rem; min-height: 2.75rem')
        ->toContain('.app-account-popover.inline-end')
        ->toContain('prefers-reduced-motion: reduce');
});
