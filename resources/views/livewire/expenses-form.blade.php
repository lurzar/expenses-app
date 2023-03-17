<div>
    {{-- Salary --}}
    <div class="grid grid-cols-3 gap-4 pb-6">
        <div>
            <x-input-label for="salary" :value="__('Salary')" />
            <x-text-input id="salary" class="block mt-1 w-full" type="number" name="salary" min="0.00" step="any" :value="old('salary')" required autofocus />
            <x-input-error :messages="$errors->get('salary')" class="mt-2" />
        </div>
        <div>
            <x-input-label for="month" :value="__('Month')" />
            <x-text-input id="month" class="block mt-1 w-full" type="text" name="month" :value="old('month')" required autofocus />
            <x-input-error :messages="$errors->get('month')" class="mt-2" />
        </div>
        <div>
            <x-input-label for="year" :value="__('Year')" />
            <x-text-input id="year" class="block mt-1 w-full" type="text" name="year" :value="old('year')" required autofocus />
            <x-input-error :messages="$errors->get('year')" class="mt-2" />
        </div>
    </div>
    {{-- Expenses items --}}
    <div class="grid grid-cols-5 gap-4 pb-6 items-end">
        <div class="col-span-3">
            <x-input-label for="expenses" :value="__('Expenses Items')" />
            <x-text-input id="expenses" class="block mt-1 w-full" type="text" name="expenses" :value="old('expenses')" required autofocus />
            <x-input-error :messages="$errors->get('expenses')" class="mt-2" />
        </div>
        <div>
            <x-input-label for="amount" :value="__('Amount')" />
            <x-text-input id="amount" class="block mt-1 w-full" type="text" name="amount" min="0.00" step="any" :value="old('amount')" required autofocus />
            <x-input-error :messages="$errors->get('amount')" class="mt-2" />
        </div>
        {{-- Button remove item --}}
        <div>
            <x-expenses-form.item-remove-button>
                {{ __('Remove Item') }}
            </x-xpenses-form.item-remove-button>
        </div>
    </div>
    {{-- Button add item --}}
    <div class="pb-6">
        <x-expenses-form.item-add-button>
            {{ __('Add Item') }}
        </x-xpenses-form.item-add-button>
    </div>
    {{-- Button submit --}}
    <div class="pb-6 float-right">
        <x-primary-button>
            {{ __('Submit') }}
        </x-primary-button>
    </div>
</div>
