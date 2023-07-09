<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl leading-tight">
            <i class="fa-solid fa-clipboard-list"></i>
            &nbsp;
            @lang('common.planning')
        </h2>
    </x-slot>

    <section id="planningList">
        <div class="mb-6 flow-root">
            <div class="float-left">
                <x-section-header 
                    :title="__('common.sentence.list_plannings')" 
                    :description="__('common.sentence.list_plannings_desc')"
                >
                    <i class="fa-solid fa-file-lines"></i>
                    &nbsp;
                </x-section-header>
            </div>
            <div class="float-right">
                <a href="{{ route('planning.create') }}">
                    <x-primary-button :icon="'create'">
                        @lang('common.create')
                    </x-primary-button>
                </a>
            </div>
        </div>
        <x-table :datasets="$plannings"/>
    </section>
</x-app-layout>
