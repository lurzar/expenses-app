<?php

namespace App\Http\Livewire;

use Livewire\Component;

class PlanningForm extends Component
{
    public $current_month;
    public $current_year;

    public $savings_items;
    public $commitments_items;
    public $others_items;

    public $salary;
    public $saving_rate;

    public $savings_values;
    public $commitments_values;
    public $others_values;

    public $total_savings;
    public $balance_after_saving_rates;
    public $balance_after_savings;

    protected $rules = [
        'salary' => 'required|numeric',
        'saving_rate' => 'required|numeric|min:20',
        'commitments_values.*.item' => 'required|string',
        'commitments_values.*.amount' => 'required|numeric',
        'others_values.*.item' => 'required|string',
        'others_values.*.amount' => 'required|numeric',
    ];

    protected $validationAttributes = [
        'commitments_values.*.item' => 'commitment item',
        'commitments_values.*.amount' => 'commitment amount',
        'others_values.*.item' => 'other item',
        'others_values.*.amount' => 'other amount',
    ];

    public function mount()
    {
        $this->fill([
            'current_month' => getCurrentMonthName(),
            'current_year' => getCurrentYear(),
            'savings_items' => collect(0),
            'commitments_items' => collect(0),
            'others_items' => collect(0),
            'salary' => '',
            'saving_rate' => 20,
            'savings_values' => [],
            'commitments_values' => [],
            'others_values' => [],
            'total_savings' => 0,
            'balance_after_saving_rates' => 0,
            'balance_after_savings' => 0,
        ]);
    }

    public function addSavingItem($data)
    {
        $this->savings_items->push(array_pop($data) + 1);
    }

    public function removeSavingItem($data)
    {
        unset($this->savings_values[$data]);
        $this->savings_items->forget($data);
    }

    public function addCommitmentItem($data)
    {
        $this->commitments_items->push(array_pop($data) + 1);
    }

    public function removeCommitmentItem($data)
    {
        unset($this->commitments_values[$data]);
        $this->commitments_items->forget($data);
    }

    public function addOtherItem($data)
    {
        $this->others_items->push(array_pop($data) + 1);
    }

    public function removeOtherItem($data)
    {
        unset($this->others_values[$data]);
        $this->others_items->forget($data);
    }

    public function updated($property)
    {
        $this->validateOnly($property);
    }

    public function updatedSalary($value)
    {
        $this->total_savings = ($value * $this->saving_rate) / 100;
        $this->balance_after_saving_rates = $value - $this->total_savings;
    }

    public function updatedSavingRate($value)
    {
        $this->total_savings = ($this->salary * $value) / 100;
        $this->balance_after_saving_rates = $this->salary - $this->total_savings;
    }
    
    public function render()
    {
        return view('livewire.planning-form');
    }
}
