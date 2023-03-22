<?php

namespace App\Http\Livewire;

use Livewire\Component;

class PlanningForm extends Component
{
    public $current_month;
    public $current_year;
    public $commitments_items;
    public $others_items;

    public function mount()
    {
        $this->current_month = getCurrentMonthName();
        $this->current_year = getCurrentYear();
        $this->commitments_items = collect(0);
        $this->others_items = collect(0);
    }

    public function addCommitmentItem($data)
    {
        $this->commitments_items->push(array_pop($data) + 1);
    }

    public function removeCommitmentItem($data)
    {
        $this->commitments_items->forget($data);
    }

    public function addOtherItem($data)
    {
        $this->others_items->push(array_pop($data) + 1);
    }

    public function removeOtherItem($data)
    {
        $this->others_items->forget($data);
    }
    
    public function render()
    {
        return view('livewire.planning-form');
    }
}
