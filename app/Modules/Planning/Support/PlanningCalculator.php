<?php

namespace App\Modules\Planning\Support;

use DomainException;

final class PlanningCalculator
{
    /**
     * @param  array<string, mixed>  $sections
     */
    public function calculate(string|int $income, string|int $savingRate, array $sections): PlanningCalculation
    {
        $incomeSen = Money::parse($income);
        $rateBasisPoints = Money::parseRate($savingRate);

        [$normalizedSections, $sectionTotals] = $this->normalizeSections($sections);
        $spending = $sectionTotals['commitments'] + $sectionTotals['others'];
        $allocated = $sectionTotals['savings'] + $spending;

        if ($allocated > $incomeSen) {
            throw new DomainException('The allocated amount cannot exceed monthly income.');
        }

        return new PlanningCalculation(
            income: Money::format($incomeSen),
            savingRate: Money::formatRate($rateBasisPoints),
            sections: $normalizedSections,
            totals: [
                'target_savings' => Money::format(Money::savingTarget($incomeSen, $rateBasisPoints)),
                'savings' => Money::format($sectionTotals['savings']),
                'commitments' => Money::format($sectionTotals['commitments']),
                'others' => Money::format($sectionTotals['others']),
                'spending' => Money::format($spending),
                'allocated' => Money::format($allocated),
                'balance' => Money::format($incomeSen - $allocated),
            ],
        );
    }

    /**
     * @param  array<string, mixed>  $sections
     * @return array{array{savings: list<array{item: string, amount: string}>, commitments: list<array{item: string, amount: string}>, others: list<array{item: string, amount: string}>}, array{savings: int, commitments: int, others: int}}
     */
    private function normalizeSections(array $sections): array
    {
        $normalized = [];
        $totals = [];

        foreach (['savings', 'commitments', 'others'] as $section) {
            $normalized[$section] = [];
            $totals[$section] = 0;
            $items = $sections[$section] ?? [];

            if (! is_array($items)) {
                $items = [];
            }

            foreach ($items as $item) {
                if (! is_array($item)) {
                    continue;
                }

                $amountSen = Money::parse((string) ($item['amount'] ?? ''));
                $normalized[$section][] = [
                    'item' => trim((string) ($item['item'] ?? '')),
                    'amount' => Money::format($amountSen),
                ];
                $totals[$section] += $amountSen;

                if ($totals[$section] > Money::MAX_SEN) {
                    throw new DomainException('A section total exceeds the supported maximum.');
                }
            }
        }

        /** @var array{savings: list<array{item: string, amount: string}>, commitments: list<array{item: string, amount: string}>, others: list<array{item: string, amount: string}>} $normalized */
        /** @var array{savings: int, commitments: int, others: int} $totals */
        return [$normalized, $totals];
    }
}
