<div>
    <form action="{{ route('planning.store') }}" method="POST">
        @csrf

        @include('livewire.planning.partials.salary')
        @include('livewire.planning.partials.saving')
        @include('livewire.planning.partials.commitment')
        @include('livewire.planning.partials.other')
        @include('livewire.planning.partials.summary')
        
        <div class="mb-6 float-right">
            <x-primary-button type="submit" :icon="'submit'">
                @lang('common.submit')
            </x-primary-button>
        </div>
    </form>
</div>