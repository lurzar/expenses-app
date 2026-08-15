# Planning cache operations

This guide helps an Expenses App maintainer configure, inspect, invalidate, troubleshoot, and roll back the Planning collection cache without exposing user financial data.

## Understand the boundary

`PlanningCache` caches the collection returned by `PlanningService::getAllPlannings()`. Dashboard, Planning, and Expenses index pages use that service method, so they share one entry for the authenticated user.

The cache is a derived read optimization. PostgreSQL remains authoritative, and the authenticated `user_id` database query remains the access-control boundary. Cache presence never proves that a user may view a Planning record.

Each entry contains serialized Planning models, including salary, sections, and totals. Treat the cache store as financial-data storage and give it the same access protection as the application database.

The implementation deliberately excludes:

- single-record Planning and Expenses routes;
- route-model binding and authorization decisions;
- authentication, profile, session, and password data;
- activity logs and Telescope diagnostics;
- validation failures and error responses; and
- a cache UI, public API, administrator role, tags, or analytics.

## Key and lifetime reference

The key format is:

```text
planning:index:v1:user:<internal-user-id>
```

For example, internal user ID `42` uses `planning:index:v1:user:42`. Laravel may prepend `CACHE_PREFIX` when the selected store supports prefixes.

The entry lives for 300 seconds. The application also invalidates it after successful mutations, so the five-minute lifetime is a recovery boundary rather than the primary consistency mechanism. Empty collections use the same key and lifetime.

Do not replace the internal numeric ID with the public user ULID without changing the implementation and its isolation tests together.

## Choose the cache store

The application uses Laravel's configured default cache store. The repository default remains:

```dotenv
CACHE_DRIVER=file
```

The file store writes beneath `storage/framework/cache/data`. Ensure the application process can read and write that directory.

Sail also provides Redis. A deployment may opt in with its approved environment configuration:

```dotenv
CACHE_DRIVER=redis
REDIS_CLIENT=phpredis
REDIS_CACHE_DB=1
```

Confirm the Redis host, authentication, transport, persistence, backup, and network controls for that environment before switching. Refresh Laravel's configuration after an environment change. Redis is optional; application tests do not require it.

Do not use cache tags or a store-specific command for Planning entries. `PlanningCache` uses only Laravel's common `remember` and `forget` operations.

## Inspect one entry safely

Use an authorized application shell in the intended environment. Work with a synthetic ID first, and inspect presence only:

```php
$cache = app(\App\Modules\Planning\Services\PlanningCache::class);
$key = $cache->indexKey(42);

config('cache.default');
$key;
cache()->has($key);
```

The expected synthetic key is `planning:index:v1:user:42`. A `true` presence result confirms only that the configured store contains an entry. It does not confirm ownership, freshness, or database persistence.

Never dump the cached value, serialized file, Redis payload, salary, sections, totals, production key inventory, credentials, or connection configuration into a terminal transcript, issue, pull request, log, or incident channel.

## Invalidate one user's entry

Resolve the correct internal numeric user ID through an authorized account lookup. Then use the application boundary:

```php
$cache = app(\App\Modules\Planning\Services\PlanningCache::class);
$cache->forgetIndex(42);
```

The next authenticated index read queries PostgreSQL and writes a fresh entry. Forgetting a missing entry is safe.

Avoid `php artisan cache:clear`, Redis `FLUSHDB`, Redis `FLUSHALL`, deleting the entire file-cache directory, or clearing a shared cache prefix. Those operations can disrupt sessions, queues, rate limits, locks, and unrelated applications.

## Automatic invalidation

| Successful application action | Invalidation point |
| --- | --- |
| Create a Planning record | `PlanningService::store()` forgets the authenticated user's entry after its database transaction returns. |
| Delete a Planning record | `PlanningService::delete()` forgets that Planning owner's entry after its database transaction returns. |
| Delete an account through the profile flow | `ProfileController::destroy()` forgets the account's entry after account and activity changes commit. |
| Restore an account | `UserObserver::restored()` restores related Planning rows, then forgets the account's entry. |

A Planning transaction that rolls back does not invalidate the existing entry. The focused tests cover this behavior.

## Troubleshoot stale or failed reads

### The index shows old data

1. Confirm the database mutation committed before retrying it.
2. Confirm the request and maintenance shell use the same environment, cache store, and cache prefix.
3. Invalidate only the affected internal user ID.
4. Reload the authenticated index and confirm it reflects PostgreSQL.
5. If the entry remains stale, verify that the mutation used one of the application paths in the invalidation table.

### The file store cannot read or write

Check ownership and permissions for `storage/framework/cache/data` against the application process. Correct the deployment permission policy; do not make the directory globally writable as a permanent fix.

### Redis is unavailable

Confirm the selected Redis cache connection and deployment network before retrying application writes. If the approved recovery is to return to the file store, change `CACHE_DRIVER`, refresh Laravel's configuration, and verify a synthetic key before restoring normal traffic.

Cache invalidation runs after the owning database transaction. A cache exception can therefore reach the user after the database write committed. Verify PostgreSQL before retrying a create or delete operation.

### A cached value cannot be decoded

Invalidate only the affected user's key. If a deployment changed PHP, Laravel, model serialization, or cache configuration, complete the rollback or forward deployment before warming entries again.

## Deploy and roll back

This cache feature adds no migration, package, environment variable, or required Redis service. Deploy the code normally, confirm the selected cache store, and verify one synthetic key without reading its value.

To roll back:

1. stop application changes that depend on the new read behavior;
2. revert the cache implementation and its documentation through the normal release process;
3. verify Planning indexes query PostgreSQL again; and
4. allow old entries to expire after five minutes, or invalidate specific affected user IDs.

Do not run a global cache flush as part of routine rollback. Once the application stops reading this namespace, remaining entries are unreachable and expire naturally.

## Validation reference

`tests/Feature/Planning/PlanningCacheTest.php` covers cache hits, per-user isolation, empty results, five-minute expiry, Planning create/delete invalidation, unaffected users, account delete/restore invalidation, and rollback behavior with Laravel's array store.

The operational contract belongs to:

- `app/Modules/Planning/Services/PlanningCache.php` for keys and lifetime;
- `app/Modules/Planning/Services/PlanningService.php` for reads and Planning mutations;
- `app/Modules/Profile/Controllers/ProfileController.php` for profile account deletion; and
- `app/Observers/UserObserver.php` for account restoration.
