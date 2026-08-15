<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Laravel\Telescope\IncomingEntry;
use Laravel\Telescope\Telescope;
use Laravel\Telescope\TelescopeApplicationServiceProvider;
use Psr\Log\LogLevel;

class TelescopeServiceProvider extends TelescopeApplicationServiceProvider
{
    /**
     * Credential-bearing keys that must never be persisted by Telescope.
     *
     * @var list<string>
     */
    private const SENSITIVE_KEYS = [
        '_token',
        'password',
        'password_confirmation',
        'current_password',
        'new_password',
        'new_password_confirmation',
        'token',
        'access_token',
        'api_token',
        'authorization',
        'cookie',
        'x_csrf_token',
        'x_xsrf_token',
        'x_api_key',
        'php_auth_pw',
    ];

    /**
     * Register any application services.
     */
    public function register(): void
    {
        Telescope::night();

        $this->hideSensitiveRequestDetails();

        Telescope::filter(function (IncomingEntry $entry): bool {
            if ($this->app->environment('local')) {
                return true;
            }

            $retained = $entry->isReportableException() ||
                        $entry->isFailedRequest() ||
                        $entry->isFailedJob() ||
                        $entry->isScheduledTask() ||
                        $this->isErrorLog($entry) ||
                        $entry->hasMonitoredTag();

            if ($retained) {
                $this->redactSensitiveEntry($entry);
            }

            return $retained;
        });
    }

    /**
     * Prevent the sensitive request details from being logged by Telescope.
     */
    protected function hideSensitiveRequestDetails(): void
    {
        if ($this->app->environment('local')) {
            return;
        }

        Telescope::hideRequestParameters([
            '_token',
            'password',
            'password_confirmation',
            'current_password',
            'new_password',
            'new_password_confirmation',
            'token',
            'access_token',
            'api_token',
        ]);

        Telescope::hideRequestHeaders([
            'authorization',
            'cookie',
            'x-csrf-token',
            'x-xsrf-token',
            'x-api-key',
        ]);
    }

    /**
     * Register the Telescope gate.
     *
     * This gate determines who can access Telescope in non-local environments.
     */
    protected function gate(): void
    {
        Gate::define('viewTelescope', function (?User $user = null): bool {
            if ($user === null || $user->email_verified_at === null) {
                return false;
            }

            $email = strtolower(trim((string) $user->email));

            return in_array($email, $this->authorizedEmails(), true);
        });
    }

    /**
     * Get the configured non-local operator emails or fail closed.
     *
     * @return list<string>
     */
    private function authorizedEmails(): array
    {
        $configured = config('telescope.authorized_emails');

        if (! is_string($configured)) {
            return [];
        }

        $emails = array_values(array_filter(
            array_map('trim', explode(',', $configured)),
            fn (string $email): bool => $email !== '',
        ));

        if ($emails === []) {
            return [];
        }

        foreach ($emails as $email) {
            if (filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
                return [];
            }
        }

        return array_values(array_unique(array_map('strtolower', $emails)));
    }

    private function isErrorLog(IncomingEntry $entry): bool
    {
        if (! $entry->isLog()) {
            return false;
        }

        return in_array(strtolower((string) ($entry->content['level'] ?? '')), [
            LogLevel::ERROR,
            LogLevel::CRITICAL,
            LogLevel::ALERT,
            LogLevel::EMERGENCY,
        ], true);
    }

    /**
     * Recursively remove credentials that top-level Telescope redaction misses.
     */
    private function redactSensitiveEntry(IncomingEntry $entry): void
    {
        if (! $entry->isRequest() && ! $entry->isLog()) {
            return;
        }

        $redacted = false;
        $entry->content = $this->redactSensitiveValues($entry->content, $redacted);

        if ($entry->isLog() && $redacted) {
            $entry->content['message'] = '[Redacted sensitive error log]';
        }
    }

    /**
     * @param  array<mixed>  $values
     * @return array<mixed>
     */
    private function redactSensitiveValues(array $values, bool &$redacted): array
    {
        foreach ($values as $key => $value) {
            $normalizedKey = str_replace('-', '_', strtolower((string) $key));

            if (in_array($normalizedKey, self::SENSITIVE_KEYS, true)) {
                $values[$key] = '********';
                $redacted = true;

                continue;
            }

            if (is_array($value)) {
                $values[$key] = $this->redactSensitiveValues($value, $redacted);
            }
        }

        return $values;
    }
}
