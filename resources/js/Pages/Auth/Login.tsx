import { FormEventHandler } from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import GuestLayout from '@/Layouts/GuestLayout';
import { route } from '@/utils/route';

interface ChangelogChange {
    category: string;
    description: string;
}

interface ChangelogRelease {
    version: string;
    status: string;
    released_at: string | null;
    changes: ChangelogChange[];
}

interface LoginProps {
    changelog: ChangelogRelease | null;
}

export default function Login({ changelog }: LoginProps) {
    const { data, setData, post, processing, errors } = useForm({
        email: '',
        password: '',
        remember: false,
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        post(route('login'));
    };

    return (
        <GuestLayout>
            <Head title="Login" />

            <form onSubmit={submit}>
                <div>
                    <label htmlFor="email" className="block text-sm font-medium text-gray-700">
                        Email
                    </label>
                    <input
                        id="email"
                        type="email"
                        value={data.email}
                        onChange={(e) => setData('email', e.target.value)}
                        className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        autoComplete="username"
                    />
                    {errors.email && <p className="mt-1 text-sm text-red-600">{errors.email}</p>}
                </div>

                <div className="mt-4">
                    <label htmlFor="password" className="block text-sm font-medium text-gray-700">
                        Password
                    </label>
                    <input
                        id="password"
                        type="password"
                        value={data.password}
                        onChange={(e) => setData('password', e.target.value)}
                        className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        autoComplete="current-password"
                    />
                    {errors.password && <p className="mt-1 text-sm text-red-600">{errors.password}</p>}
                </div>

                <div className="mt-4 flex items-center">
                    <input
                        id="remember"
                        type="checkbox"
                        checked={data.remember}
                        onChange={(e) => setData('remember', e.target.checked)}
                        className="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                    />
                    <label htmlFor="remember" className="ml-2 text-sm text-gray-600">
                        Remember me
                    </label>
                </div>

                <div className="mt-6 flex items-center justify-between">
                    <Link
                        href={route('password.request')}
                        className="text-sm text-indigo-600 hover:text-indigo-500"
                    >
                        Forgot your password?
                    </Link>

                    <button
                        type="submit"
                        disabled={processing}
                        className="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 disabled:opacity-50"
                    >
                        Login
                    </button>
                </div>

                <div className="mt-4 text-center text-sm text-gray-600">
                    Don't have an account?{' '}
                    <Link href={route('register')} className="text-indigo-600 hover:text-indigo-500">
                        Register
                    </Link>
                </div>
            </form>

            {changelog && (
                <section
                    aria-labelledby="changelog-heading"
                    className="mt-6 border-t border-gray-200 pt-5 dark:border-gray-700"
                >
                    <div className="flex flex-wrap items-baseline justify-between gap-2">
                        <h2 id="changelog-heading" className="text-base font-semibold text-gray-900 dark:text-gray-100">
                            What's new in v{changelog.version}
                        </h2>
                        <p className="text-xs font-medium uppercase tracking-wide text-indigo-600 dark:text-indigo-400">
                            {changelog.status}
                            {changelog.released_at && (
                                <>
                                    {' · '}
                                    <time dateTime={changelog.released_at}>{changelog.released_at}</time>
                                </>
                            )}
                        </p>
                    </div>

                    <ul className="mt-3 space-y-3">
                        {changelog.changes.map((change, index) => (
                            <li key={`${change.category}-${index}`} className="text-sm text-gray-600 dark:text-gray-300">
                                <span className="mr-2 font-medium text-gray-900 dark:text-gray-100">
                                    {change.category}:
                                </span>
                                {change.description}
                            </li>
                        ))}
                    </ul>
                </section>
            )}
        </GuestLayout>
    );
}
