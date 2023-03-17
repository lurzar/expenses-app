<?php

namespace App\Http\Livewire;

use Livewire\Component;

class ExpensesForm extends Component
{
    public $expenses_items;

    public function mount()
    {
        $this->expenses_items = collect(0);
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
