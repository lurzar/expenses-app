<?php

namespace App\Modules\Admin\Navigation;

final readonly class AdminNavigationItem
{
    public function __construct(
        public string $key,
        public string $labelKey,
        public string $descriptionKey,
        public string $routeName,
        public string $ability,
    ) {}
}
