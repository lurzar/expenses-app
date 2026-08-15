<?php

test('the test runtime cannot inherit an external database URL', function () {
    expect(config('database.default'))->toBe('sqlite')
        ->and(config('database.connections.sqlite.url'))->toBeNull()
        ->and(config('database.connections.sqlite.database'))->toBe(':memory:');
});
