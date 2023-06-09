<div>
    @include('livewire.planning.partials.salary')
    @include('livewire.planning.partials.saving')
    @include('livewire.planning.partials.commitment')
    @include('livewire.planning.partials.other')
    @include('livewire.planning.partials.summary')
    
    <input type="hidden" name="month" id="month" wire:model="current_month">
    <input type="hidden" name="year" id="year" wire:model="current_year">
    <input type="hidden" name="totals[saving]" wire:model="total_savings">
    <input type="hidden" name="totals[balance]" wire:model="total_balance">
    <input type="hidden" name="totals[commitment]" wire:model="total_commitment">
    <input type="hidden" name="totals[other]" wire:model="total_other">
</div>