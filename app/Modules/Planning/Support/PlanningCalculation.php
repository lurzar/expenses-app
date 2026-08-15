<?php

namespace App\Modules\Planning\Support;

final readonly class PlanningCalculation
{
    /**
     * @param  array{savings: list<array{item: string, amount: string}>, commitments: list<array{item: string, amount: string}>, others: list<array{item: string, amount: string}>}  $sections
     * @param  array{target_savings: string, savings: string, commitments: string, others: string, spending: string, allocated: string, balance: string}  $totals
     */
    public function __construct(
        public string $income,
        public string $savingRate,
        public array $sections,
        public array $totals,
    ) {}
}
