<?php

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;

// Get list of months
// Return array
if (! function_exists('getMonthList')) {
    /** @return Collection<int, string> */
    function getMonthList(): Collection
    {
        $data = collect();

        foreach (range(1, 12) as $month) {
            $data->push(Carbon::parse("2000-{$month}-01")->format('F'));
        }

        return $data;
    }
}

// Get list of years
// Return array
if (! function_exists('getYearList')) {
    /** @return Collection<int, int> */
    function getYearList(): Collection
    {
        $data = collect();

        foreach (range(2023, 2034) as $year) {
            $data->push($year);
        }

        return $data;
    }
}

// Get current month's name
// Return e.g. March
if (! function_exists('getCurrentMonthName')) {
    function getCurrentMonthName(): string
    {
        $data = Carbon::now()->format('F');

        return $data;
    }
}

// Get current year
// Return e.g. 2023
if (! function_exists('getCurrentYear')) {
    function getCurrentYear(): string
    {
        $data = Carbon::now()->format('Y');

        return $data;
    }
}

// Get current month's name
// Return e.g. March 2023
if (! function_exists('getCurrentMonthYear')) {
    function getCurrentMonthYear(): string
    {
        $data = Carbon::now()->format('F Y');

        return $data;
    }
}

// Check allow to unlock form
// Return bool
if (! function_exists('unlockForm')) {
    function unlockForm(): bool
    {
        $setting_open_date = 7; // will get from db settings;
        $today = (int) Carbon::now()->format('d');

        return $today === $setting_open_date;
    }
}

// Get open date
// Return e.g. 7 March 2023
if (! function_exists('getOpenDate')) {
    function getOpenDate(): string
    {
        $setting_open_date = 7; // will get from db settings;
        $data = $setting_open_date.' '.getCurrentMonthYear();

        return $data;
    }
}

// Get close date
// Return e.g. 14 March 2023
if (! function_exists('getCloseDate')) {
    function getCloseDate(): string
    {
        $setting_close_date = 14; // will get from db settings;
        $data = $setting_close_date.' '.getCurrentMonthYear();

        return $data;
    }
}

// Get current module name
// Return e.g. planning
if (! function_exists('getCurrentModule')) {
    function getCurrentModule(): string
    {
        $data = explode('.', Route::currentRouteName() ?? '')[0];

        return $data;
    }
}
