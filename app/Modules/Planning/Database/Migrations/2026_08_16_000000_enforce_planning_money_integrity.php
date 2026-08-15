<?php

use App\Modules\Planning\Support\Money;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const ACTIVE_PERIOD_INDEX = 'plannings_user_period_active_unique';

    public function up(): void
    {
        Schema::table('plannings', function (Blueprint $table): void {
            $table->unsignedTinyInteger('month_number')->nullable();
            $table->unsignedSmallInteger('year_number')->nullable();
            $table->decimal('salary_decimal', 14, 2)->nullable();
            $table->decimal('saving_rate', 5, 2)->nullable();
        });

        DB::table('plannings')->orderBy('id')->each(function (object $row): void {
            $incomeSen = $this->legacyMoneyToSen($row->salary);
            $sections = $this->normalizeSections($this->decodeJson($row->sections));
            $sectionTotals = $this->sectionTotals($sections);
            $legacyTotals = $this->decodeJson($row->totals);
            $targetSen = $this->legacyMoneyToSen(
                $legacyTotals['target_savings'] ?? $legacyTotals['saving'] ?? 0,
            );

            if ($targetSen > $incomeSen) {
                throw new RuntimeException(sprintf(
                    'Planning %s has legacy target savings above income and must be resolved before migration.',
                    $row->planning_id,
                ));
            }

            $rateBasisPoints = $incomeSen === 0
                ? 0
                : intdiv(($targetSen * 10_000) + intdiv($incomeSen, 2), $incomeSen);
            $spending = $sectionTotals['commitments'] + $sectionTotals['others'];
            $allocated = $sectionTotals['savings'] + $spending;

            DB::table('plannings')->where('id', $row->id)->update([
                'month_number' => $this->monthNumber($row->month, (string) $row->planning_id),
                'year_number' => $this->yearNumber($row->year),
                'salary_decimal' => Money::format($incomeSen),
                'saving_rate' => Money::formatRate($rateBasisPoints),
                'sections' => json_encode($sections, JSON_THROW_ON_ERROR),
                'totals' => json_encode([
                    'target_savings' => Money::format(Money::savingTarget($incomeSen, $rateBasisPoints)),
                    'savings' => Money::format($sectionTotals['savings']),
                    'commitments' => Money::format($sectionTotals['commitments']),
                    'others' => Money::format($sectionTotals['others']),
                    'spending' => Money::format($spending),
                    'allocated' => Money::format($allocated),
                    'balance' => $this->formatSigned($incomeSen - $allocated),
                ], JSON_THROW_ON_ERROR),
            ]);
        });

        $duplicate = DB::table('plannings')
            ->select(['user_id', 'month_number', 'year_number'])
            ->whereNull('deleted_at')
            ->groupBy(['user_id', 'month_number', 'year_number'])
            ->havingRaw('COUNT(*) > 1')
            ->first();

        if ($duplicate !== null) {
            throw new RuntimeException('Duplicate active Planning periods must be resolved before the money-integrity migration can continue.');
        }

        Schema::table('plannings', function (Blueprint $table): void {
            $table->dropColumn(['month', 'year', 'salary']);
        });
        Schema::table('plannings', function (Blueprint $table): void {
            $table->renameColumn('month_number', 'month');
            $table->renameColumn('year_number', 'year');
            $table->renameColumn('salary_decimal', 'salary');
        });
        Schema::table('plannings', function (Blueprint $table): void {
            $table->unsignedTinyInteger('month')->nullable(false)->change();
            $table->unsignedSmallInteger('year')->nullable(false)->change();
            $table->decimal('salary', 14, 2)->nullable(false)->change();
            $table->decimal('saving_rate', 5, 2)->nullable(false)->change();
        });

        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE plannings ADD CONSTRAINT plannings_month_range_check CHECK (month BETWEEN 1 AND 12)');
            DB::statement('ALTER TABLE plannings ADD CONSTRAINT plannings_year_range_check CHECK (year BETWEEN 2000 AND 2100)');
            DB::statement('ALTER TABLE plannings ADD CONSTRAINT plannings_salary_range_check CHECK (salary BETWEEN 0 AND 999999999999.99)');
            DB::statement('ALTER TABLE plannings ADD CONSTRAINT plannings_saving_rate_range_check CHECK (saving_rate BETWEEN 0 AND 100.00)');
        }

        DB::statement(sprintf(
            'CREATE UNIQUE INDEX %s ON plannings (user_id, month, year) WHERE deleted_at IS NULL',
            self::ACTIVE_PERIOD_INDEX,
        ));
    }

    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS '.self::ACTIVE_PERIOD_INDEX);

        if (DB::getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE plannings DROP CONSTRAINT IF EXISTS plannings_month_range_check');
            DB::statement('ALTER TABLE plannings DROP CONSTRAINT IF EXISTS plannings_year_range_check');
            DB::statement('ALTER TABLE plannings DROP CONSTRAINT IF EXISTS plannings_salary_range_check');
            DB::statement('ALTER TABLE plannings DROP CONSTRAINT IF EXISTS plannings_saving_rate_range_check');
        }

        Schema::table('plannings', function (Blueprint $table): void {
            $table->string('month_legacy')->nullable();
            $table->string('year_legacy')->nullable();
            $table->float('salary_legacy')->nullable();
        });

        DB::table('plannings')->orderBy('id')->each(function (object $row): void {
            DB::table('plannings')->where('id', $row->id)->update([
                'month_legacy' => $this->monthName((int) $row->month),
                'year_legacy' => (string) $row->year,
                'salary_legacy' => (float) $row->salary,
            ]);
        });

        Schema::table('plannings', function (Blueprint $table): void {
            $table->dropColumn(['month', 'year', 'salary', 'saving_rate']);
        });
        Schema::table('plannings', function (Blueprint $table): void {
            $table->renameColumn('month_legacy', 'month');
            $table->renameColumn('year_legacy', 'year');
            $table->renameColumn('salary_legacy', 'salary');
        });
        Schema::table('plannings', function (Blueprint $table): void {
            $table->string('month')->nullable(false)->change();
            $table->string('year')->nullable(false)->change();
            $table->float('salary')->nullable(false)->change();
        });
    }

    private function monthNumber(mixed $month, string $planningId): int
    {
        $value = trim((string) $month);

        if (preg_match('/^(?:0?[1-9]|1[0-2])$/D', $value) === 1) {
            return (int) $value;
        }

        return match ($value) {
            'January' => 1,
            'February' => 2,
            'March' => 3,
            'April' => 4,
            'May' => 5,
            'June' => 6,
            'July' => 7,
            'August' => 8,
            'September' => 9,
            'October' => 10,
            'November' => 11,
            'December' => 12,
            default => throw new RuntimeException(sprintf(
                'Planning %s contains invalid legacy month "%s" and must be resolved before migration.',
                $planningId,
                $value,
            )),
        };
    }

    private function yearNumber(mixed $year): int
    {
        $value = filter_var($year, FILTER_VALIDATE_INT);

        if ($value === false || $value < 2000 || $value > 2100) {
            throw new RuntimeException('Planning contains an invalid legacy year.');
        }

        return $value;
    }

    private function monthName(int $month): string
    {
        return match ($month) {
            1 => 'January',
            2 => 'February',
            3 => 'March',
            4 => 'April',
            5 => 'May',
            6 => 'June',
            7 => 'July',
            8 => 'August',
            9 => 'September',
            10 => 'October',
            11 => 'November',
            12 => 'December',
            default => throw new RuntimeException('Planning contains an invalid month during rollback.'),
        };
    }

    /** @return array<string, mixed> */
    private function decodeJson(mixed $value): array
    {
        if (is_array($value)) {
            return $value;
        }

        $decoded = json_decode((string) $value, true, flags: JSON_THROW_ON_ERROR);

        return is_array($decoded) ? $decoded : [];
    }

    /**
     * @param  array<string, mixed>  $sections
     * @return array{savings: list<array{item: string, amount: string}>, commitments: list<array{item: string, amount: string}>, others: list<array{item: string, amount: string}>}
     */
    private function normalizeSections(array $sections): array
    {
        $normalized = ['savings' => [], 'commitments' => [], 'others' => []];

        foreach (array_keys($normalized) as $section) {
            $items = is_array($sections[$section] ?? null) ? $sections[$section] : [];

            foreach ($items as $item) {
                if (! is_array($item)) {
                    continue;
                }

                $normalized[$section][] = [
                    'item' => trim((string) ($item['item'] ?? '')),
                    'amount' => Money::format($this->legacyMoneyToSen($item['amount'] ?? 0)),
                ];
            }
        }

        return $normalized;
    }

    /**
     * @param  array{savings: list<array{item: string, amount: string}>, commitments: list<array{item: string, amount: string}>, others: list<array{item: string, amount: string}>}  $sections
     * @return array{savings: int, commitments: int, others: int}
     */
    private function sectionTotals(array $sections): array
    {
        $totals = ['savings' => 0, 'commitments' => 0, 'others' => 0];

        foreach ($totals as $section => $_) {
            foreach ($sections[$section] as $item) {
                $totals[$section] += Money::parse($item['amount']);
            }

            if ($totals[$section] > Money::MAX_SEN) {
                throw new RuntimeException('Planning contains a section total above the supported maximum.');
            }
        }

        return $totals;
    }

    private function legacyMoneyToSen(mixed $value): int
    {
        if (! is_numeric($value)) {
            throw new RuntimeException('Planning contains invalid legacy money.');
        }

        return Money::parse(number_format((float) $value, 2, '.', ''));
    }

    private function formatSigned(int $sen): string
    {
        return $sen < 0 ? '-'.Money::format(abs($sen)) : Money::format($sen);
    }
};
