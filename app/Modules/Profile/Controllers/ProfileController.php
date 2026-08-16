<?php

namespace App\Modules\Profile\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\ActivityLog\ActivityEvent;
use App\Modules\ActivityLog\Services\ActivityRecorder;
use App\Modules\Authorization\Exceptions\SuperAdminLifecycleException;
use App\Modules\Authorization\RoleName;
use App\Modules\Authorization\Services\SuperAdminLifecycleService;
use App\Modules\Planning\Services\PlanningCache;
use App\Modules\Profile\Requests\ProfileUpdateRequest;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    public function __construct(
        private readonly ActivityRecorder $activityRecorder,
        private readonly PlanningCache $planningCache,
        private readonly SuperAdminLifecycleService $superAdminLifecycle,
    ) {}

    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): Response
    {
        return Inertia::render('Profile/Edit', [
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status' => session('status'),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user instanceof User, 401);
        $attributes = $request->validated();

        try {
            if ($this->willLoseProtectedActiveStatus($user, $attributes)) {
                $this->superAdminLifecycle->mutateActiveStatus(
                    $user,
                    $user,
                    ActivityEvent::AccountProfileUpdated,
                    fn (User $target): array => [
                        'changed_fields' => $this->applyProfileUpdate($target, $attributes),
                    ],
                );
            } else {
                DB::transaction(function () use ($attributes, $user): void {
                    $changedFields = $this->applyProfileUpdate($user, $attributes);

                    if ($changedFields !== []) {
                        $this->activityRecorder->record(
                            ActivityEvent::AccountProfileUpdated,
                            $user->user_id,
                            'account',
                            $user->user_id,
                            ['changed_fields' => $changedFields],
                        );
                    }
                });
            }
        } catch (SuperAdminLifecycleException $exception) {
            throw ValidationException::withMessages(['email' => $exception->getMessage()]);
        }

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();
        abort_unless($user instanceof User, 401);

        try {
            if ($user->hasRole(RoleName::SuperAdmin->value)) {
                $this->superAdminLifecycle->mutateActiveStatus(
                    $user,
                    $user,
                    ActivityEvent::AccountDeleted,
                    function (User $target): array {
                        $target->delete();

                        return [];
                    },
                );
            } else {
                DB::transaction(function () use ($user): void {
                    $user->delete();

                    $this->activityRecorder->record(
                        ActivityEvent::AccountDeleted,
                        $user->user_id,
                        'account',
                        $user->user_id,
                    );
                });
            }
        } catch (SuperAdminLifecycleException $exception) {
            throw ValidationException::withMessages(['account' => $exception->getMessage()])
                ->errorBag('userDeletion');
        }

        $this->planningCache->forgetIndex((int) $user->getKey());

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @return list<string>
     */
    private function applyProfileUpdate(User $user, array $attributes): array
    {
        $user->fill($attributes);
        $changedFields = array_values(array_intersect(
            ['name', 'email'],
            array_keys($user->getDirty()),
        ));

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return $changedFields;
    }

    /** @param array<string, mixed> $attributes */
    private function willLoseProtectedActiveStatus(User $user, array $attributes): bool
    {
        return $user->email_verified_at !== null
            && isset($attributes['email'])
            && $attributes['email'] !== $user->email
            && $user->hasRole(RoleName::SuperAdmin->value);
    }
}
