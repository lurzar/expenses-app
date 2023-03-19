<?php

// Get list of months
if (! function_exists('get_months')) {
    function get_months() {
        $data = collect();

        foreach (range(1, 12) as $month) {
            $data->push(date('F', mktime(0, 0, 0, $month, 1)));
        }

        return $data;
    }
}

// Get list of years
if (! function_exists('get_years')) {
    function get_years() {
        $data = collect();

        foreach (range(2023, 2035) as $year) {
            $data->push($year);
        }

        return $data;
    }
}