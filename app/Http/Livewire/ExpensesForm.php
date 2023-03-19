<?php

namespace App\Http\Livewire;

use Livewire\Component;

class ExpensesForm extends Component
{
    public $months;
    public $years;
    public $commitments_items;
    public $others_items;

    public function mount()
    {
        $this->months = get_months();
        $this->years = get_years();
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
        return view('livewire.expenses-form');
    }
}
