<?php

use Carbon\Carbon;
use Illuminate\Support\Facades\Route;

// Get list of months
// Return array
if (! function_exists('getMonthList')) {
    function getMonthList() {
        $data = collect();

        foreach (range(1, 12) as $month) {
            $data->push(date('F', mktime(0, 0, 0, $month, 1)));
        }

        return $data;
    }
}

// Get list of years
// Return array
if (! function_exists('getYearList')) {
    function getYearList() {
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
    function getCurrentMonthName() {
        $data = Carbon::now()->format('F');
        return $data;
    }
}

// Get current year
// Return e.g. 2023
if (! function_exists('getCurrentYear')) {
    function getCurrentYear() {
        $data = Carbon::now()->format('Y');
        return $data;
    }
}

// Get current month's name
// Return e.g. March 2023
if (! function_exists('getCurrentMonthYear')) {
    function getCurrentMonthYear() {
        $data = Carbon::now()->format('F Y');
        return $data;
    }
}

// Check allow to unlock form
// Return bool
if (! function_exists('unlockForm')) {
    function unlockForm() {
        $setting_open_date = 7; // will get from db settings;
        $setting_close_date = 14; // will get from db settings;
        $today = (int) Carbon::now()->format('d');
        return ($today == $setting_open_date && $today <= $setting_close_date) ? true : false;
    }
}

// Get open date
// Return e.g. 7 March 2023
if (! function_exists('getOpenDate')) {
    function getOpenDate() {
        $setting_open_date = 7; // will get from db settings;
        $data =  $setting_open_date.' '.getCurrentMonthYear();
        return $data;
    }
}

// Get close date
// Return e.g. 14 March 2023
if (! function_exists('getCloseDate')) {
    function getCloseDate() {
        $setting_close_date = 14; // will get from db settings;
        $data =  $setting_close_date.' '.getCurrentMonthYear();
        return $data;
    }
}

// Get current module name
// Return e.g. planning
if (! function_exists('getCurrentModule')) {
    function getCurrentModule() {
        $data = explode('.', Route::currentRouteName())[0];
        return $data;
    }
}