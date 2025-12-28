<?php

namespace App\Modules\Expenses;

use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class ExpensesServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/routes.php');
        
        Livewire::component('expenses', \App\Modules\Expenses\Livewire\Expenses::class);
    }
}
