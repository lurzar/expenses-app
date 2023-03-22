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
        $month = Carbon::now()->format('F');
        return $month;
    }
}

// Get current year
if (! function_exists('getCurrentYear')) {
    function getCurrentYear() {
        $year = Carbon::now()->format('Y');
        return $year;
    }
}