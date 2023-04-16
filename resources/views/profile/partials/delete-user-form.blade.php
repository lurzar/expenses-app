<section class="space-y-6">
    <x-section-header 
        :title="__('account.delete')" 
        :description="__('account.delete_warn_full')"
        :show_balance="false"
    >
        <i class="fa-solid fa-user-slash"></i>
        &nbsp;
    </x-section-header>

    <x-danger-button
        x-data=""
        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
        :icon="'delete'"
    >@lang('account.delete')</x-danger-button>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" class="p-6">
            @csrf
            @method('delete')

            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                <i class="fa-solid fa-triangle-exclamation text-red-600 dark:text-red-400"></i>
                &nbsp;
                @lang('account.delete_confirmation')
            </h2>

            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                @lang('account.delete_warn')
                @lang('password.ask_enter')
            </p>

            <div class="mt-6">
                <x-input-label for="password_delete" value="{{ __('label.password') }}" class="sr-only" />

                <x-text-input
                    id="password_delete"
                    name="password"
                    type="password"
                    class="mt-1 block w-3/4"
                    placeholder="{{ __('label.password') }}"
                />

                <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2" />
            </div>

            <div class="mt-6 flex justify-end">
                <x-secondary-button x-on:click="$dispatch('close')" :icon="'cancel'">
                    @lang('common.cancel')
                </x-secondary-button>

                <x-danger-button class="ml-3" :icon="'delete'">
                    @lang('account.delete')
                </x-danger-button>
            </div>
        </form>
    </x-modal>
</section>
