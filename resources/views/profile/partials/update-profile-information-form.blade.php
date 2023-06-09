<div class="card bg-base-100 mb-6">
    <div class="card-body">
        <x-section-header 
            :title="__('account.information')" 
            :description="__('account.information_desc')"
        >
            <i class="fa-solid fa-id-card"></i>
            &nbsp;
        </x-section-header>

        <form id="send-verification" method="post" action="{{ route('verification.send') }}">
            @csrf
        </form>

        <form method="post" action="{{ route('profile.update') }}" class="space-y-4">
            @csrf
            @method('patch')

            <div>
                <x-input-label for="name">
                    <i class="fa-solid fa-user"></i>
                    &nbsp;
                    @lang('label.name')
                </x-input-label>
                <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $user->name)" required autofocus autocomplete="name" />
                <x-input-error class="mt-2" :messages="$errors->get('name')" />
            </div>

            <div>
                <x-input-label for="email">
                    <i class="fa-solid fa-envelope"></i>
                    &nbsp;
                    @lang('label.email')
                </x-input-label>
                <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $user->email)" required autocomplete="username" />
                <x-input-error class="mt-2" :messages="$errors->get('email')" />

                @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                    <div>
                        <p class="text-sm mt-2 text-gray-800 dark:text-gray-200">
                            @lang('email.unverified')

                            <button form="send-verification" class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800">
                                @lang('email.verify_link_resend')
                            </button>
                        </p>

                        @if (session('status') === 'verification-link-sent')
                            <p class="mt-2 font-medium text-sm text-green-600 dark:text-green-400">
                                @lang('email.verify_link_sent')
                            </p>
                        @endif
                    </div>
                @endif
            </div>

            <div class="mt-6 float-right">
                <x-primary-button type="submit" :icon="'save'">
                    @lang('common.save')
                </x-primary-button>
            </div>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-green-600 dark:text-green-400"
                >@lang('common.saved')</p>
            @endif
        </form>
    </div>
</div>
