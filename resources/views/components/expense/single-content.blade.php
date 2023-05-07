<section id="{{ $expenses->slug }}">
    <x-section-header 
        :show_total_balance="false"
    >
        <i class="fa-solid fa-money-bill-trend-up"></i>
        &nbsp;
        @lang('common.expenses') {{ $expenses->month }}, {{ $expenses->year }}
    </x-section-header>
    @dd($expenses)
</section>