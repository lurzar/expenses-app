<?php

namespace App\Http\Middleware;

use App\Data\AuthenticatedUserData;
use App\Modules\Authorization\Permissions\SystemPermission;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     */
    public function share(Request $request): array
    {
        $user = $request->user();

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user ? AuthenticatedUserData::fromModel($user) : null,
                'capabilities' => [
                    'access_admin' => $user?->can(SystemPermission::AccessAdmin->value) ?? false,
                ],
            ],
            'locale' => app()->getLocale(),
            'translations' => $this->getTranslations(),
            'flash' => [
                'message' => fn () => $request->session()->get('message'),
                'success' => fn () => $request->session()->get('success'),
                'warning' => fn () => $request->session()->get('warning'),
                'error' => fn () => $request->session()->get('error'),
            ],
        ];
    }

    /**
     * Get translations for the current locale.
     *
     * @return array<string, mixed>
     */
    protected function getTranslations(): array
    {
        $locale = app()->getLocale();
        $langPath = lang_path($locale);
        $translations = [];

        if (is_dir($langPath)) {
            foreach (glob($langPath.'/*.php') ?: [] as $file) {
                $key = basename($file, '.php');
                $translations[$key] = __($key);
            }
        }

        return $translations;
    }
}
