<section>
    <x-section-header 
        :title="__('password.update')" 
        :description="__('password.update_desc')"
        :show_balance="false"
    >
        <i class="fa-solid fa-key"></i>
        &nbsp;
    </x-section-header>

    <form method="post" action="{{ route('password.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('put')

        <div>
            <x-input-label for="current_password">
                <i class="fa-solid fa-user-lock"></i>
                &nbsp;
                @lang('label.current_password')
            </x-input-label>
            <x-text-input id="current_password" name="current_password" type="password" class="mt-1 block w-full" autocomplete="current-password" />
            <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="password">
                <i class="fa-solid fa-lock"></i>
                &nbsp;
                @lang('label.new_password')
            </x-input-label>
            <x-text-input id="password" name="password" type="password" class="mt-1 block w-full" autocomplete="new-password" />
            <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="confirm_password">
                <i class="fa-solid fa-circle-check"></i>
                &nbsp;
                @lang('label.confirm_password')
            </x-input-label>
            <x-text-input id="password_confirmation" name="password_confirmation" type="password" class="mt-1 block w-full" autocomplete="new-password" />
            <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button :icon="'update'">
                @lang('common.update')
            </x-primary-button>

            @if (session('status') === 'password-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-green-600 dark:text-green-400"
                >@lang('common.saved')</p>
            @endif
        </div>
    </form>
</section>
