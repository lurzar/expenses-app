<?php

namespace App\Traits;

use Illuminate\Support\Str;

trait HasPublicId
{
    /**
     * Get the column name for the public ID.
     * Override in model to customize (e.g., 'user_id', 'planning_id').
     */
    public function publicIdColumn(): string
    {
        return Str::singular($this->getTable()) . '_id';
    }

    /**
     * Boot the trait.
     */
    public static function bootHasPublicId(): void
    {
        static::creating(function ($model) {
            $column = $model->publicIdColumn();
            if (empty($model->{$column})) {
                $model->{$column} = (string) Str::ulid();
            }
        });
    }

    /**
     * Get the route key name for Laravel route model binding.
     * Uses the public ID column (ULID) for routing.
     */
    public function getRouteKeyName(): string
    {
        return $this->publicIdColumn();
    }
}
