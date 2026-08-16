<?php

namespace App\Modules\Authorization\Console;

use App\Modules\Authorization\Services\AuthorizationSynchronizer;
use Illuminate\Console\Command;

class SyncAuthorization extends Command
{
    protected $signature = 'authorization:sync';

    protected $description = 'Synchronize the code-owned authorization catalog and protected roles';

    public function handle(AuthorizationSynchronizer $synchronizer): int
    {
        $result = $synchronizer->sync();

        $this->info('Authorization catalog synchronized.');

        if ($result->hasDrift()) {
            $this->components->warn('Authorization drift requires operator review:');

            foreach ($result->drift() as $drift) {
                $this->line(" - {$drift}");
            }

            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
