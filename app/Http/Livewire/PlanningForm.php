<?php

namespace App\Http\Livewire;

use Livewire\Component;

class PlanningForm extends Component
{
    public $current_month;
    public $current_year;
    public $commitments_items;
    public $others_items;

    public $salary;
    public $saving_rate;
    public $commitments_values;
    public $others_values;

    public $balance_after_saving_rate;

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
            'commitments_items' => collect(0),
            'others_items' => collect(0),
            'salary' => '',
            'saving_rate' => 20,
            'commitments_values' => [],
            'others_values' => [],
            'balance_after_saving_rate' => 0,
        ]);
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
        $this->balance_after_saving_rate = ($value * $this->saving_rate) / 100;
    }

    public function updatedSavingRate($value)
    {
        $this->balance_after_saving_rate = ($this->salary * $value) / 100;
    }
    
    public function render()
    {
        return view('livewire.planning-form');
    }
}
