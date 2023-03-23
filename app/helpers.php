<?php

use Carbon\Carbon;

// Get list of months
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
if (! function_exists('getCurrentMonthName')) {
    function getCurrentMonthName() {
        $data = Carbon::now()->format('F');
        return $data;
    }
}

// Get current year
if (! function_exists('getCurrentYear')) {
    function getCurrentYear() {
        $data = Carbon::now()->format('Y');
        return $data;
    }
}

// Get current month's name
if (! function_exists('getCurrentMonthYear')) {
    function getCurrentMonthYear() {
        $data = Carbon::now()->format('F Y');
        return $data;
    }
}

// Check allow to unlock form
if (! function_exists('checkUnlockForm')) {
    function checkUnlockForm($param = null) {
        $allowed_date = $param;
        $today = (int) Carbon::now()->format('d');
        
        return ($today == $allowed_date) ? true : false;
    }
}