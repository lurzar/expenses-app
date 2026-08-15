import { FormEventHandler } from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import GuestLayout from '@/Layouts/GuestLayout';
import { route } from '@/utils/route';

interface ForgotPasswordProps {
    status: string | null;
}

export default function ForgotPassword({ status }: ForgotPasswordProps) {
    const { data, setData, post, processing, errors } = useForm({ email: '' });

    const submit: FormEventHandler = (event) => {
        event.preventDefault();
        post(route('password.email'));
    };

    return (
        <GuestLayout>
            <Head title="Forgot password" />

            <p className="mb-4 text-sm text-gray-600 dark:text-gray-300">
                Enter your email address and we will send you a password reset link.
            </p>

            {status && <p className="mb-4 text-sm font-medium text-green-600">{status}</p>}

            <form onSubmit={submit}>
                <label htmlFor="email" className="block text-sm font-medium text-gray-700 dark:text-gray-200">
                    Email
                </label>
                <input
                    id="email"
                    type="email"
                    value={data.email}
                    onChange={(event) => setData('email', event.target.value)}
                    className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                    autoComplete="username"
                    required
                    autoFocus
                />
                {errors.email && <p className="mt-1 text-sm text-red-600">{errors.email}</p>}

                <div className="mt-6 flex items-center justify-between">
                    <Link href={route('login')} className="text-sm text-indigo-600 hover:text-indigo-500">
                        Back to login
                    </Link>
                    <button
                        type="submit"
                        disabled={processing}
                        className="rounded-md bg-indigo-600 px-4 py-2 text-white hover:bg-indigo-700 disabled:opacity-50"
                    >
                        Email reset link
                    </button>
                </div>
            </form>
        </GuestLayout>
    );
}
