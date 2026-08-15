<?php

namespace App\Data;

use App\Models\User;
use DateTimeInterface;

final class AuthenticatedUserData
{
    /**
     * @return array{
     *     user_id: string,
     *     name: string,
     *     email: string,
     *     email_verified_at: string|null,
     *     created_at: string|null,
     *     updated_at: string|null
     * }
     */
    public static function fromModel(User $user): array
    {
        return [
            'user_id' => (string) $user->getAttribute('user_id'),
            'name' => (string) $user->getAttribute('name'),
            'email' => (string) $user->getAttribute('email'),
            'email_verified_at' => self::timestamp($user->getAttribute('email_verified_at')),
            'created_at' => self::timestamp($user->getAttribute('created_at')),
            'updated_at' => self::timestamp($user->getAttribute('updated_at')),
        ];
    }

    private static function timestamp(mixed $value): ?string
    {
        return $value instanceof DateTimeInterface ? $value->format(DateTimeInterface::ATOM) : null;
    }
}
