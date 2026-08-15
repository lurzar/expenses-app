<?php

use App\Models\User;
use App\Providers\AppServiceProvider;
use App\Providers\TelescopeServiceProvider as AppTelescopeServiceProvider;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Laravel\Telescope\EntryType;
use Laravel\Telescope\IncomingEntry;
use Laravel\Telescope\Telescope;
use Laravel\Telescope\TelescopeServiceProvider;

beforeEach(function () {
    Telescope::$filterUsing = [];
    Telescope::$hiddenRequestHeaders = ['authorization', 'php-auth-pw'];
    Telescope::$hiddenRequestParameters = ['password', 'password_confirmation'];
});

function useNonLocalEnvironment(): void
{
    app()->detectEnvironment(fn () => 'production');
}

function registerApplicationTelescopeProvider(): void
{
    $provider = new AppTelescopeServiceProvider(app());
    $provider->register();
    $provider->boot();
}

test('non-local telescope registration follows loaded configuration', function () {
    useNonLocalEnvironment();
    config()->set('telescope.enabled', true);

    (new AppServiceProvider(app()))->register();

    expect(app()->getProvider(TelescopeServiceProvider::class))->not->toBeNull()
        ->and(app()->getProvider(AppTelescopeServiceProvider::class))->not->toBeNull();
});

test('non-local telescope remains unregistered when loaded configuration disables it', function () {
    useNonLocalEnvironment();
    config()->set('telescope.enabled', false);

    (new AppServiceProvider(app()))->register();

    expect(app()->getProvider(TelescopeServiceProvider::class))->toBeNull()
        ->and(app()->getProvider(AppTelescopeServiceProvider::class))->toBeNull();
});

test('non-local telescope fails closed when loaded enablement is malformed', function () {
    useNonLocalEnvironment();
    config()->set('telescope.enabled', 'typo');

    (new AppServiceProvider(app()))->register();

    expect(app()->getProvider(TelescopeServiceProvider::class))->toBeNull()
        ->and(app()->getProvider(AppTelescopeServiceProvider::class))->toBeNull();
});

test('local telescope remains available when its loaded configuration is disabled', function () {
    app()->detectEnvironment(fn () => 'local');
    config()->set('telescope.enabled', false);

    (new AppServiceProvider(app()))->register();

    expect(app()->getProvider(TelescopeServiceProvider::class))->not->toBeNull()
        ->and(app()->getProvider(AppTelescopeServiceProvider::class))->not->toBeNull();
});

test('non-local telescope routes deny guests when enabled', function () {
    useNonLocalEnvironment();
    config()->set([
        'telescope.enabled' => true,
        'telescope.authorized_emails' => 'operator@example.com',
    ]);

    (new AppServiceProvider(app()))->register();

    $this->get('/telescope')->assertForbidden();
});

test('a verified allowlisted operator can view non-local telescope', function () {
    useNonLocalEnvironment();
    config()->set('telescope.authorized_emails', ' first@example.com, OPERATOR@example.com ');
    registerApplicationTelescopeProvider();
    $operator = User::factory()->create(['email' => 'operator@example.com']);

    expect(Gate::forUser($operator)->allows('viewTelescope'))->toBeTrue();
});

test('non-local telescope rejects unverified unlisted and malformed authorization', function () {
    useNonLocalEnvironment();
    $verified = User::factory()->create(['email' => 'operator@example.com']);
    $unverified = User::factory()->unverified()->create(['email' => 'unverified@example.com']);

    config()->set('telescope.authorized_emails', 'other@example.com');
    registerApplicationTelescopeProvider();
    expect(Gate::forUser($verified)->allows('viewTelescope'))->toBeFalse();

    config()->set('telescope.authorized_emails', 'operator@example.com,not-an-email');
    registerApplicationTelescopeProvider();
    expect(Gate::forUser($verified)->allows('viewTelescope'))->toBeFalse();

    config()->set('telescope.authorized_emails', 'unverified@example.com');
    registerApplicationTelescopeProvider();
    expect(Gate::forUser($unverified)->allows('viewTelescope'))->toBeFalse();
});

test('non-local request diagnostics redact approved credentials and headers', function () {
    useNonLocalEnvironment();
    registerApplicationTelescopeProvider();

    expect(Telescope::$hiddenRequestParameters)->toContain(
        '_token',
        'password',
        'password_confirmation',
        'current_password',
        'token',
        'access_token',
        'api_token',
    )->and(Telescope::$hiddenRequestHeaders)->toContain(
        'authorization',
        'cookie',
        'x-csrf-token',
        'x-xsrf-token',
        'x-api-key',
    );
});

test('non-local retained requests recursively redact nested credentials', function () {
    useNonLocalEnvironment();
    registerApplicationTelescopeProvider();
    $filter = Telescope::$filterUsing[array_key_last(Telescope::$filterUsing)];
    $request = IncomingEntry::make([
        'response_status' => 500,
        'payload' => [
            'credentials' => [
                'password' => 'nested-password',
                'token' => 'nested-token',
            ],
            'safe' => 'diagnostic-value',
        ],
        'session' => [
            'nested' => ['access-token' => 'session-token'],
        ],
        'headers' => [
            'x-api-key' => 'request-api-key',
        ],
    ])->type(EntryType::REQUEST);

    expect($filter($request))->toBeTrue()
        ->and($request->content['payload']['credentials']['password'])->toBe('********')
        ->and($request->content['payload']['credentials']['token'])->toBe('********')
        ->and($request->content['session']['nested']['access-token'])->toBe('********')
        ->and($request->content['headers']['x-api-key'])->toBe('********')
        ->and($request->content['payload']['safe'])->toBe('diagnostic-value');
});

test('non-local retained error logs redact nested context and interpolated messages', function () {
    useNonLocalEnvironment();
    registerApplicationTelescopeProvider();
    $filter = Telescope::$filterUsing[array_key_last(Telescope::$filterUsing)];
    $log = IncomingEntry::make([
        'level' => 'error',
        'message' => 'Authentication failed with token raw-log-token',
        'context' => [
            'credentials' => ['token' => 'raw-log-token'],
            'safe' => 'diagnostic-value',
        ],
    ])->type(EntryType::LOG);

    expect($filter($log))->toBeTrue()
        ->and($log->content['context']['credentials']['token'])->toBe('********')
        ->and($log->content['message'])->not->toContain('raw-log-token')
        ->and($log->content['context']['safe'])->toBe('diagnostic-value');
});

test('non-local filtering retains failures and error logs without routine noise', function () {
    useNonLocalEnvironment();
    registerApplicationTelescopeProvider();
    $filter = Telescope::$filterUsing[array_key_last(Telescope::$filterUsing)];

    $failedRequest = IncomingEntry::make(['response_status' => 500])->type(EntryType::REQUEST);
    $failedJob = IncomingEntry::make(['status' => 'failed'])->type(EntryType::JOB);
    $errorLog = IncomingEntry::make(['level' => 'error'])->type(EntryType::LOG);
    $debugLog = IncomingEntry::make(['level' => 'debug'])->type(EntryType::LOG);
    $successfulRequest = IncomingEntry::make(['response_status' => 200])->type(EntryType::REQUEST);

    expect($filter($failedRequest))->toBeTrue()
        ->and($filter($failedJob))->toBeTrue()
        ->and($filter($errorLog))->toBeTrue()
        ->and($filter($debugLog))->toBeFalse()
        ->and($filter($successfulRequest))->toBeFalse();
});

test('disabled telescope has no pruning schedule', function () {
    $pruneEvents = collect(app(Schedule::class)->events())
        ->filter(fn ($event) => Str::contains($event->command ?? '', 'telescope:prune'));

    expect($pruneEvents)->toBeEmpty();
});

test('enabled telescope uses configured daily pruning', function () {
    config()->set([
        'telescope.enabled' => true,
        'telescope.prune_hours' => 168,
    ]);

    require base_path('routes/console.php');

    $event = collect(app(Schedule::class)->events())
        ->first(fn ($event) => Str::contains($event->command ?? '', 'telescope:prune --hours=168'));

    expect($event)->not->toBeNull()
        ->and($event->expression)->toBe('0 0 * * *');
});

test('malformed pruning retention fails closed to the documented default', function () {
    config()->set([
        'telescope.enabled' => true,
        'telescope.prune_hours' => 'invalid',
    ]);

    require base_path('routes/console.php');

    $event = collect(app(Schedule::class)->events())
        ->first(fn ($event) => Str::contains($event->command ?? '', 'telescope:prune --hours=168'));

    expect($event)->not->toBeNull();
});

test('configured pruning deletes expired entries and retains recent entries', function () {
    useNonLocalEnvironment();
    config()->set('telescope.enabled', true);
    (new AppServiceProvider(app()))->register();

    DB::table('telescope_entries')->insert([
        [
            'uuid' => (string) Str::uuid(),
            'batch_id' => (string) Str::uuid(),
            'type' => EntryType::LOG,
            'content' => '{}',
            'created_at' => now()->subHours(169),
        ],
        [
            'uuid' => (string) Str::uuid(),
            'batch_id' => (string) Str::uuid(),
            'type' => EntryType::LOG,
            'content' => '{}',
            'created_at' => now()->subHours(167),
        ],
    ]);

    $this->artisan('telescope:prune', ['--hours' => 168])->assertSuccessful();

    expect(DB::table('telescope_entries')->count())->toBe(1);
});

test('example environment documents safe telescope controls', function () {
    $environment = file_get_contents(base_path('.env.example'));

    expect($environment)->toContain(
        'TELESCOPE_AUTHORIZED_EMAILS=',
        'TELESCOPE_PRUNE_HOURS=168',
    );
});
