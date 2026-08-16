<?php

namespace App\Modules\Admin\Navigation;

use App\Models\User;
use InvalidArgumentException;

final class AdminNavigationRegistry
{
    /** @var array<string, AdminNavigationItem> */
    private array $items = [];

    public function register(AdminNavigationItem $item): void
    {
        if (isset($this->items[$item->key])) {
            throw new InvalidArgumentException("Admin navigation item [{$item->key}] is already registered.");
        }

        $this->items[$item->key] = $item;
    }

    /**
     * @return list<array{key: string, label: string, description: string, href: string}>
     */
    public function availableTo(User $user): array
    {
        $items = [];

        foreach ($this->items as $item) {
            if (! $user->can($item->ability)) {
                continue;
            }

            $items[] = [
                'key' => $item->key,
                'label' => __($item->labelKey),
                'description' => __($item->descriptionKey),
                'href' => route($item->routeName),
            ];
        }

        return $items;
    }
}
