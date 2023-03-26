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
    public $commitments_values;
    public $others_values;

    protected $rules = [
        'salary' => 'required|numeric',
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
            'commitments_values' => [],
            'others_values' => [],
        ]);
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

    public function updated($property)
    {
        $this->validateOnly($property);
    }
    
    public function render()
    {
        return view('livewire.planning-form');
    }
}
