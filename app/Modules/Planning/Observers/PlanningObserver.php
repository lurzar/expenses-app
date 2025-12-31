<?php

namespace App\Modules\Planning\Observers;

use App\Modules\Planning\Models\Planning;
use Illuminate\Support\Str;

class PlanningObserver
{
    /**
     * Handle the Planning "creating" event.
     */
    public function creating(Planning $planning): void
    {
        if (empty($planning->slug)) {
            $slugId = (string) Str::ulid();
            $planning->slug = Str::of($slugId.' planning '.$planning->month.' '.$planning->year)->slug('-');
        }
    }
}
