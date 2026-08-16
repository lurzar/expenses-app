<?php

namespace App\Modules\ActivityLog\Services;

use App\Modules\ActivityLog\ActivityEvent;
use App\Modules\ActivityLog\Models\ActivityLog;
use Illuminate\Support\Str;
use InvalidArgumentException;

class ActivityRecorder
{
    /** @param array<string, mixed> $metadata */
    public function record(
        ActivityEvent $event,
        ?string $actorId,
        string $subjectType,
        ?string $subjectId,
        array $metadata = [],
    ): ?ActivityLog {
        if (config('activity-log.enabled') !== true) {
            return null;
        }

        if ($subjectType !== $event->subjectType()) {
            throw new InvalidArgumentException('Activity subject type does not match its event.');
        }

        if (($actorId !== null && ! Str::isUlid($actorId)) || ($subjectId !== null && ! Str::isUlid($subjectId))) {
            throw new InvalidArgumentException('Activity identifiers must use public ULIDs.');
        }

        $metadata = $this->validatedMetadata($event, $metadata);

        return ActivityLog::query()->create([
            'event' => $event->value,
            'actor_id' => $actorId,
            'subject_type' => $subjectType,
            'subject_id' => $subjectId,
            'metadata' => $metadata,
        ]);
    }

    /**
     * Keep every event's metadata inside its explicit data-minimization contract.
     *
     * @param  array<string, mixed>  $metadata
     * @return array<string, mixed>|null
     */
    private function validatedMetadata(ActivityEvent $event, array $metadata): ?array
    {
        if ($event === ActivityEvent::AuthorizationCatalogSynchronized) {
            return $this->catalogMetadata($metadata);
        }

        if (in_array($event, [ActivityEvent::AuthorizationRoleAssigned, ActivityEvent::AuthorizationRoleRemoved], true)) {
            if (array_keys($metadata) !== ['role'] || ! is_string($metadata['role'])) {
                throw new InvalidArgumentException('Role activity accepts one role name only.');
            }

            return ['role' => $metadata['role']];
        }

        if ($event !== ActivityEvent::AccountProfileUpdated) {
            if ($metadata !== []) {
                throw new InvalidArgumentException('This activity event accepts no metadata.');
            }

            return null;
        }

        if (array_keys($metadata) !== ['changed_fields'] || ! is_array($metadata['changed_fields'])) {
            throw new InvalidArgumentException('Profile activity accepts changed field names only.');
        }

        $changedFields = array_values(array_unique($metadata['changed_fields']));
        $approvedFields = ['name', 'email'];

        if ($changedFields === [] || array_diff($changedFields, $approvedFields) !== []) {
            throw new InvalidArgumentException('Profile activity contains an unapproved field name.');
        }

        return ['changed_fields' => $changedFields];
    }

    /**
     * @param  array<string, mixed>  $metadata
     * @return array{
     *     created_permissions: list<string>,
     *     created_roles: list<string>,
     *     added_role_permissions: list<string>,
     *     drift: list<string>
     * }
     */
    private function catalogMetadata(array $metadata): array
    {
        $expectedKeys = ['created_permissions', 'created_roles', 'added_role_permissions', 'drift'];

        if (array_keys($metadata) !== $expectedKeys) {
            throw new InvalidArgumentException('Catalog activity metadata does not match its allowlist.');
        }

        return [
            'created_permissions' => $this->stringList($metadata['created_permissions']),
            'created_roles' => $this->stringList($metadata['created_roles']),
            'added_role_permissions' => $this->stringList($metadata['added_role_permissions']),
            'drift' => $this->stringList($metadata['drift']),
        ];
    }

    /** @return list<string> */
    private function stringList(mixed $value): array
    {
        if (! is_array($value) || ! array_is_list($value)) {
            throw new InvalidArgumentException('Catalog activity metadata accepts string lists only.');
        }

        foreach ($value as $item) {
            if (! is_string($item)) {
                throw new InvalidArgumentException('Catalog activity metadata accepts string lists only.');
            }
        }

        return $value;
    }
}
