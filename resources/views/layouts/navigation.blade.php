<div class="navbar bg-base-200">
    <div class="navbar-start">
        @auth
            <!-- Responsive Navigation Links -->
            <div class="dropdown">
                <label tabindex="0" class="btn btn-ghost lg:hidden">
                    <svg 
                        xmlns="http://www.w3.org/2000/svg" 
                        class="h-5 w-5" 
                        fill="none" 
                        viewBox="0 0 24 24" 
                        stroke="currentColor"
                    >
                        <path 
                            stroke-linecap="round"
                            stroke-linejoin="round" 
                            stroke-width="2"
                            d="M4 6h16M4 12h8m-8 6h16" 
                        />
                    </svg>
                </label>
                <ul tabindex="0" class="menu menu-compact dropdown-content mt-3 p-2 shadow bg-base-100 rounded-box w-52">
                    <li>
                        <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                            @lang('common.dashboard')
                        </x-nav-link>
                    </li>
                    <li>
                        <x-nav-link :href="route('planning.index')" :active="request()->routeIs('planning.index')">
                            @lang('common.planning')
                        </x-nav-link>
                    </li>
                    <li>
                        <x-nav-link :href="route('expenses.index')" :active="request()->routeIs('expenses.index')">
                            @lang('common.expenses')
                        </x-nav-link>
                    </li>
                </ul>
            </div>
        @endauth

        <!-- Logo -->
        <a href="@auth {{ route('dashboard') }} @else {{ route('landing') }} @endauth" class="btn btn-ghost normal-case text-xl">
            <x-application-logo class="block h-9 w-auto fill-current" />
        </a>
    </div>
    <div class="navbar-center hidden lg:flex">
        <!-- Navigation Links -->
        @auth
            <ul class="menu menu-horizontal px-1">
                <li>
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        @lang('common.dashboard')
                    </x-nav-link>
                </li>
                <li>
                    <x-nav-link :href="route('planning.index')" :active="request()->routeIs('planning.index')">
                        @lang('common.planning')
                    </x-nav-link>
                </li>
                <li>
                    <x-nav-link :href="route('expenses.index')" :active="request()->routeIs('expenses.index')">
                        @lang('common.expenses')
                    </x-nav-link>
                </li>
            </ul>
        @endauth
    </div>
    <div class="navbar-end">
        <!-- Utilities Menu (Responsived) -->
        <x-compiled.theme-option />
        <x-compiled.language-option />
        @auth
            <x-compiled.settings-option />
        @endauth
    </div>
    </div>
</div>
