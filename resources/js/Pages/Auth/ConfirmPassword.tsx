import { FormEventHandler } from 'react';
import { Head, useForm } from '@inertiajs/react';
import GuestLayout from '@/Layouts/GuestLayout';
import { route } from '@/utils/route';

export default function ConfirmPassword() {
    const { data, setData, post, processing, errors } = useForm({ password: '' });

    const submit: FormEventHandler = (event) => {
        event.preventDefault();
        post(route('password.confirm'));
    };

    return (
        <GuestLayout>
            <Head title="Confirm password" />

            <p className="mb-4 text-sm text-gray-600 dark:text-gray-300">
                Confirm your password before continuing to this secure area.
            </p>

            <form onSubmit={submit}>
                <label htmlFor="password" className="block text-sm font-medium text-gray-700 dark:text-gray-200">
                    Password
                </label>
                <input
                    id="password"
                    type="password"
                    value={data.password}
                    onChange={(event) => setData('password', event.target.value)}
                    className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    autoComplete="current-password"
                    required
                    autoFocus
                />
                {errors.password && <p className="mt-1 text-sm text-red-600">{errors.password}</p>}

                <button
                    type="submit"
                    disabled={processing}
                    className="mt-6 w-full rounded-md bg-indigo-600 px-4 py-2 text-white hover:bg-indigo-700 disabled:opacity-50"
                >
                    Confirm
                </button>
            </form>
        </GuestLayout>
    );
}
