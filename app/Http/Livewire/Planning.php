<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Illuminate\Support\Arr;

class Planning extends Component
{
    // Model
    public $current_month;
    public $current_year;
    public $salary;
    public $saving_rate;
    public $savings_values;
    public $commitments_values;
    public $others_values;

    // Variables
    public $total_savings;
    public $balance_after_saving_rates;
    public $balance_after_savings;
    public $balance_after_commitments;
    public $total_balance;

    // Control fields
    public $savings_fields;
    public $commitments_fields;
    public $others_fields;

    protected $rules = [
        'salary' => 'required|numeric',
        'saving_rate' => 'required|numeric|min:20|max:100',
        'savings_values.*.item' => 'required|string',
        'savings_values.*.amount' => 'required|numeric|min:1',
        'commitments_values.*.item' => 'required|string',
        'commitments_values.*.amount' => 'required|numeric|min:1',
        'others_values.*.item' => 'required|string',
        'others_values.*.amount' => 'required|numeric|min:1',
    ];

    protected $validationAttributes = [
        'savings_values.*.item' => 'saving item :position',
        'savings_values.*.amount' => 'saving amount :position',
        'commitments_values.*.item' => 'commitment item :position',
        'commitments_values.*.amount' => 'commitment amount :position',
        'others_values.*.item' => 'other item :position',
        'others_values.*.amount' => 'other amount :position',
    ];

    public function mount()
    {
        $this->fill([
            'current_month' => getCurrentMonthName(),
            'current_year' => getCurrentYear(),
            'salary' => '',
            'saving_rate' => 20,
            'savings_values' => [],
            'commitments_values' => [],
            'others_values' => [],
            'total_savings' => 0,
            'balance_after_saving_rates' => 0,
            'balance_after_savings' => 0,
            'balance_after_commitments' => 0,
            'total_balance' => 0,
            'savings_fields' => collect(0),
            'commitments_fields' => collect(0),
            'others_fields' => collect(0),
        ]);
    }

    public function updated($property)
    {
        $this->validateOnly($property);
    }

    public function updatedSalary($value)
    {
        if (!blank($value) && !blank($this->saving_rate) && $this->saving_rate <= 100) {
            $this->total_savings = ($value * $this->saving_rate) / 100;
            $this->balance_after_saving_rates = $value - $this->total_savings;
        }
    }

    public function updatedSavingRate($value)
    {
        if (!blank($value) && !blank($this->salary) && $value <= 100) {
            $this->total_savings = ($this->salary * $value) / 100;
            $this->balance_after_saving_rates = $this->salary - $this->total_savings;
        }
    }

    public function addSavingField($data)
    {
        $this->savings_fields->push(array_pop($data) + 1);
    }

    public function removeSavingField($data)
    {
        unset($this->savings_values[$data]);
        $this->savings_fields->forget($data);
    }

    public function updatedSavingsValues($field_value)
    {
        $counted = $this->countTotal($this->savings_values);

        if ($counted > $this->total_savings) {
            $this->addError('savings_amount_limit', 'Your savings amount exceed the total savings');
        } else {
            $this->balance_after_savings = $this->salary - $counted;
        }
    }

    public function addCommitmentField($data)
    {
        $this->commitments_fields->push(array_pop($data) + 1);
    }

    public function removeCommitmentField($data)
    {
        unset($this->commitments_values[$data]);
        $this->commitments_fields->forget($data);
    }

    public function updatedCommitmentsValues()
    {
        $counted = $this->countTotal($this->commitments_values);

        if ($counted > $this->balance_after_savings) {
            $this->addError('commitments_amount_limit', 'Your commitments amount exceed the balance');
        } else {
            $this->balance_after_commitments = $this->balance_after_savings - $counted;
        }
    }

    public function addOtherField($data)
    {
        $this->others_fields->push(array_pop($data) + 1);
    }

    public function removeOtherField($data)
    {
        unset($this->others_values[$data]);
        $this->others_fields->forget($data);
    }

    public function updatedOthersValues()
    {
        $counted = $this->countTotal($this->others_values);

        if ($counted > $this->balance_after_commitments) {
            $this->addError('others_amount_limit', 'Your others amount exceed the balance');
        } else {
            $this->total_balance = $this->balance_after_commitments - $counted;
        }
    }

    public function render()
    {
        return view('livewire.planning.index');
    }

    private function countTotal($value): int
    {
        $result = 0;
        $amounts = Arr::pluck($value, 'amount');

        foreach ($amounts as $amount) {
            $result += ((int) $amount);
        }

        return $result;
    }
}
