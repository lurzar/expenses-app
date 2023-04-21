<div>
    <form action="{{ route('planning.store') }}" method="POST">
        @csrf

        @include('livewire.planning.partials.salary')
        @include('livewire.planning.partials.saving')
        @include('livewire.planning.partials.commitment')
        @include('livewire.planning.partials.other')
        @include('livewire.planning.partials.summary')
        
        <input type="hidden" name="month" id="month" wire:model="current_month">
        <input type="hidden" name="year" id="year" wire:model="current_year">
        <input type="hidden" name="total_saving" wire:model="total_savings">
        <input type="hidden" name="total_balance" wire:model="total_balance">
        <input type="hidden" name="total_commitment" wire:model="total_commitment">
        <input type="hidden" name="total_other" wire:model="total_other">

        <div class="mb-6 float-right">
            <x-primary-button type="submit" :icon="'submit'">
                @lang('common.submit')
            </x-primary-button>
        </div>
    </form>
</div>