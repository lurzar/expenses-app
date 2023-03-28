<?php

namespace App\Http\Livewire\Planning;

use Livewire\Component;

class SalaryInformation extends Component
{
    public $current_month;
    public $current_year;
    
    public function render()
    {
        return view('livewire.planning.salary-information');
    }
}
