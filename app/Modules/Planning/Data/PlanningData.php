<?php

namespace App\Modules\Planning\Data;

use App\Modules\Planning\Models\Planning;
use DateTimeInterface;

/**
 * @phpstan-type PlanningPayload array{
 *     planning_id: string,
 *     month: int,
 *     year: int,
 *     salary: string,
 *     saving_rate: string,
 *     sections: array<string, mixed>,
 *     totals: array<string, float|int|string>,
 *     name: string,
 *     spending: string,
 *     created_at: string|null,
 *     updated_at: string|null
 * }
 */
final class PlanningData
{
    /** @return PlanningPayload */
    public static function fromModel(Planning $planning): array
    {
        return [
            'planning_id' => $planning->planning_id,
            'month' => $planning->month,
            'year' => $planning->year,
            'salary' => $planning->salary,
            'saving_rate' => $planning->saving_rate,
            'sections' => $planning->sections?->all() ?? [],
            'totals' => $planning->totals?->all() ?? [],
            'name' => $planning->name,
            'spending' => $planning->spending,
            'created_at' => self::timestamp($planning->getAttribute('created_at')),
            'updated_at' => self::timestamp($planning->getAttribute('updated_at')),
        ];
    }

    /**
     * @param  iterable<Planning>  $plannings
     * @return list<PlanningPayload>
     */
    public static function collection(iterable $plannings): array
    {
        $payload = [];

        foreach ($plannings as $planning) {
            $payload[] = self::fromModel($planning);
        }

        return $payload;
    }

    private static function timestamp(mixed $value): ?string
    {
        return $value instanceof DateTimeInterface ? $value->format(DateTimeInterface::ATOM) : null;
    }
}
