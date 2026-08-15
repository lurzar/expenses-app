<?php

namespace App\Modules\ActivityLog\Console;

use App\Modules\ActivityLog\Models\ActivityLog;
use Illuminate\Console\Command;

class PruneActivityLogs extends Command
{
    protected $signature = 'activity-log:prune {--days= : Number of days to retain}';

    protected $description = 'Prune expired activity-log entries';

    public function handle(): int
    {
        $days = $this->retentionDays();

        if ($days === null) {
            $this->error('The --days option must be a positive integer.');

            return self::FAILURE;
        }

        $deleted = ActivityLog::query()
            ->where('created_at', '<', now()->subDays($days))
            ->delete();

        $label = $deleted === 1 ? 'entry' : 'entries';
        $this->info("{$deleted} activity {$label} pruned.");

        return self::SUCCESS;
    }

    private function retentionDays(): ?int
    {
        $requested = $this->option('days');

        if ($requested === null) {
            return filter_var(
                config('activity-log.retention_days', 365),
                FILTER_VALIDATE_INT,
                ['options' => ['min_range' => 1]],
            ) ?: 365;
        }

        $validated = filter_var(
            $requested,
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 1]],
        );

        return $validated === false ? null : $validated;
    }
}
