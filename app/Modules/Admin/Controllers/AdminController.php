<?php

namespace App\Modules\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\Admin\Navigation\AdminNavigationRegistry;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

final class AdminController extends Controller
{
    public function __construct(
        private readonly AdminNavigationRegistry $navigation,
    ) {}

    public function index(Request $request): Response
    {
        /** @var User $user */
        $user = $request->user();

        return Inertia::render('Admin/Index', [
            'admin' => [
                'navigation' => $this->navigation->availableTo($user),
            ],
        ]);
    }
}
