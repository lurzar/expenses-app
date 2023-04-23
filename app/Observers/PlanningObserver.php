<?php

namespace App\Observers;

use App\Models\Planning;
use Illuminate\Support\Str;

class PlanningObserver
{
    public function creating(Planning $planning)
    {
        $planning->slug = Str::of('planning '.$planning->month.' '.$planning->year.' '.$planning->user_id.'')->slug('-');
    }
}
