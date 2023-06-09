<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl leading-tight">
            <i class="fa-solid fa-gauge-high"></i>
            &nbsp;
            @lang('common.dashboard')
        </h2>
    </x-slot>

    <div class="stats stats-horizontal shadow w-full">
        <div class="stat">
            <div class="stat-figure text-primary place-items-center">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="inline-block w-8 h-8 stroke-current"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path></svg>
            </div>
            <div class="stat-title">Total Savings</div>
            <div class="stat-value text-primary">RM 0{{-- number_format((floatval($expenses->totals['savings'])),2) --}}</div>
            <div class="stat-desc">21% more than last month</div>
        </div>
        
        <div class="stat">
            <div class="stat-figure text-secondary place-items-center">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="inline-block w-8 h-8 stroke-current"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
            </div>
            <div class="stat-title">Total Commitments</div>
            <div class="stat-value text-secondary">RM 0{{-- number_format((floatval($expenses->totals['commitments'])),2) --}}</div>
            <div class="stat-desc">21% more than last month</div>
        </div>
        
        <div class="stat">
            <div class="stat-figure text-secondary place-items-center">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="inline-block w-8 h-8 stroke-current"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
            </div>
            <div class="stat-title">Total Others</div>
            <div class="stat-value text-secondary">RM 0{{-- number_format((floatval($expenses->totals['others'])),2) --}}</div>
            <div class="stat-desc">21% more than last month</div>
        </div>

        {{-- <div class="stat">
            <div class="stat-figure text-secondary">
                <div class="avatar online">
                    <div class="w-16 rounded-full">
                        <img src="{{ asset('images/avatar.png') }}" alt="Avatar">
                    </div>
                </div>
            </div>
            <div class="stat-value">
                {{ $expenses->total_spent }}
            </div>
            <div class="stat-title">Spent</div>
            <div class="stat-desc text-secondary">86% more than last month</div>
        </div> --}}
    </div>
</x-app-layout>
