<?php

namespace App\Observers;

use App\Models\Planning;
use Illuminate\Support\Str;

class PlanningObserver
{
    public function creating(Planning $planning)
    {
        $slugId = (string) Str::ulid();
        $planning->slug = Str::of(''.$slugId.' planning '.$planning->month.' '.$planning->year.'')->slug('-');
    }
}
