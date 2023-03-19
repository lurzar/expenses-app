<?php

namespace App\Http\Livewire;

use Livewire\Component;

class ExpensesForm extends Component
{
    public $months;
    public $years;
    public $commitments_items;
    public $expenses_items;

    public function mount()
    {
        $this->months = get_months();
        $this->years = get_years();
        $this->commitments_items = collect(0);
        $this->expenses_items = collect(0);
    }

    public function addCommitmentItem($data)
    {
        $this->commitments_items->push(array_pop($data) + 1);
    }

    public function removeCommitmentItem($data)
    {
        $this->commitments_items->forget($data);
    }

    public function addExpensesItem($data)
    {
        $this->expenses_items->push(array_pop($data) + 1);
    }

    public function removeExpensesItem($data)
    {
        $this->expenses_items->forget($data);
    }

    public function render()
    {
        return view('livewire.expenses-form');
    }
}
