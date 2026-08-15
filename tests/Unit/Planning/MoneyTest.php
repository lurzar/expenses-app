<?php

use App\Modules\Planning\Support\Money;

test('money parses accepted MYR decimals into sen and formats canonically', function (string $input, int $sen, string $decimal) {
    expect(Money::parse($input))->toBe($sen)
        ->and(Money::format($sen))->toBe($decimal);
})->with([
    ['0', 0, '0.00'],
    ['0.1', 10, '0.10'],
    ['12.34', 1234, '12.34'],
    ['999999999999.99', 99_999_999_999_999, '999999999999.99'],
]);

test('money rejects malformed or out of range values', function (string $input) {
    Money::parse($input);
})->with([
    '-1',
    '1.001',
    '1e3',
    'NaN',
    'Infinity',
    '1000000000000.00',
])->throws(InvalidArgumentException::class);

test('saving target rounds half up once to the nearest sen', function () {
    expect(Money::savingTarget(5, 1000))->toBe(1)
        ->and(Money::savingTarget(10001, 1250))->toBe(1250)
        ->and(Money::savingTarget(10001, 10000))->toBe(10001);
});
