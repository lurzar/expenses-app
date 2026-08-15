<?php

namespace App\Modules\Planning\Support;

use InvalidArgumentException;

final class Money
{
    public const MAX_DECIMAL = '999999999999.99';

    public const MAX_SEN = 99_999_999_999_999;

    public static function parse(string|int $value): int
    {
        $decimal = (string) $value;

        if (! preg_match('/^(?:0|[1-9]\d{0,11})(?:\.\d{1,2})?$/', $decimal)) {
            throw new InvalidArgumentException('Money must be a non-negative MYR decimal with at most two fractional digits.');
        }

        [$whole, $fraction] = array_pad(explode('.', $decimal, 2), 2, '');
        $sen = ((int) $whole * 100) + (int) str_pad($fraction, 2, '0');

        if ($sen > self::MAX_SEN) {
            throw new InvalidArgumentException('Money exceeds the supported maximum.');
        }

        return $sen;
    }

    public static function format(int $sen): string
    {
        if ($sen < 0 || $sen > self::MAX_SEN) {
            throw new InvalidArgumentException('Money is outside the supported range.');
        }

        return intdiv($sen, 100).'.'.str_pad((string) ($sen % 100), 2, '0', STR_PAD_LEFT);
    }

    public static function parseRate(string|int $value): int
    {
        $decimal = (string) $value;

        if (! preg_match('/^(?:0|[1-9]\d?|100)(?:\.\d{1,2})?$/', $decimal)) {
            throw new InvalidArgumentException('Saving rate must have at most two fractional digits.');
        }

        [$whole, $fraction] = array_pad(explode('.', $decimal, 2), 2, '');
        $basisPoints = ((int) $whole * 100) + (int) str_pad($fraction, 2, '0');

        if ($basisPoints > 10_000) {
            throw new InvalidArgumentException('Saving rate must not exceed 100.00.');
        }

        return $basisPoints;
    }

    public static function formatRate(int $basisPoints): string
    {
        if ($basisPoints < 0 || $basisPoints > 10_000) {
            throw new InvalidArgumentException('Saving rate is outside the supported range.');
        }

        return intdiv($basisPoints, 100).'.'.str_pad((string) ($basisPoints % 100), 2, '0', STR_PAD_LEFT);
    }

    public static function savingTarget(int $incomeSen, int $rateBasisPoints): int
    {
        if ($incomeSen < 0 || $incomeSen > self::MAX_SEN || $rateBasisPoints < 0 || $rateBasisPoints > 10_000) {
            throw new InvalidArgumentException('Saving target inputs are outside the supported range.');
        }

        return intdiv(($incomeSen * $rateBasisPoints) + 5_000, 10_000);
    }
}
