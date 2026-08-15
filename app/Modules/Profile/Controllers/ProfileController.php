<?php

namespace App\Modules\Profile\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\ActivityLog\ActivityEvent;
use App\Modules\ActivityLog\Services\ActivityRecorder;
use App\Modules\Planning\Services\PlanningCache;
use App\Modules\Profile\Requests\ProfileUpdateRequest;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    public function __construct(
        private readonly ActivityRecorder $activityRecorder,
        private readonly PlanningCache $planningCache,
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
        DB::transaction(function () use ($request): void {
            $user = $request->user();
            abort_unless($user instanceof User, 401);

            $user->fill($request->validated());
            $changedFields = array_values(array_intersect(
                ['name', 'email'],
                array_keys($user->getDirty()),
            ));

            if ($user->isDirty('email')) {
                $user->email_verified_at = null;
            }

            $user->save();

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

        DB::transaction(function () use ($user): void {
            $user->delete();

            $this->activityRecorder->record(
                ActivityEvent::AccountDeleted,
                $user->user_id,
                'account',
                $user->user_id,
            );
        });

        $this->planningCache->forgetIndex((int) $user->getKey());

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
