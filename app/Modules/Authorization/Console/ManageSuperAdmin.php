<?php

namespace App\Modules\Authorization\Console;

use App\Models\User;
use App\Modules\Authorization\Exceptions\SuperAdminLifecycleException;
use App\Modules\Authorization\Services\SuperAdminLifecycleService;
use Illuminate\Console\Command;

final class ManageSuperAdmin extends Command
{
    protected $signature = 'authorization:super-admin
                            {user : Target account public ULID}
                            {--replace= : Existing super-admin public ULID to rotate out}
                            {--force : Skip the interactive confirmation}';

    protected $description = 'Grant or rotate protected super-admin access for verified accounts';

    public function handle(SuperAdminLifecycleService $lifecycle): int
    {
        $publicId = (string) $this->argument('user');

        $target = User::query()->withTrashed()->where('user_id', $publicId)->first();

        if (! $target instanceof User) {
            $this->error('The target account is not eligible for super-admin access.');

            return self::FAILURE;
        }

        $replacePublicId = $this->option('replace');
        $previous = $this->replacement($replacePublicId);

        if ($replacePublicId !== null && ! $previous instanceof User) {
            return self::FAILURE;
        }

        $confirmation = $previous instanceof User
            ? "Rotate super-admin access from account {$previous->user_id} to account {$publicId}?"
            : "Grant super-admin access to account {$publicId}?";

        if (! $this->option('force') && ! $this->confirm($confirmation)) {
            $this->warn('Super-admin access was not changed.');

            return self::FAILURE;
        }

        try {
            if ($previous instanceof User) {
                $lifecycle->rotate($previous, $target);
                $this->info("Super-admin access rotated to account {$publicId}.");

                return self::SUCCESS;
            }

            $changed = $lifecycle->grant($target);
        } catch (SuperAdminLifecycleException $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        if (! $changed) {
            $this->info("Account {$publicId} already has super-admin access.");

            return self::SUCCESS;
        }

        $this->info("Super-admin access granted for account {$publicId}.");

        return self::SUCCESS;
    }

    private function replacement(mixed $publicId): ?User
    {
        if ($publicId === null) {
            return null;
        }

        $previous = User::query()->withTrashed()->where('user_id', (string) $publicId)->first();

        if (! $previous instanceof User) {
            $this->error('The previous account does not have super-admin access.');
        }

        return $previous;
    }
}
