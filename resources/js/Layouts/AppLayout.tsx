import { PropsWithChildren, ReactNode } from 'react';
import { Link, usePage } from '@inertiajs/react';
import { User, PageProps } from '@/types';
import { route } from '@/utils/route';
import ThemeToggle from '@/Components/ThemeToggle';

interface AppLayoutProps {
    user: User;
    header?: ReactNode;
}

interface AppPageProps extends PageProps {
    locale?: string;
}

// Helper function to safely get translation
const t = (translations: Record<string, unknown> | undefined, key: string, fallback: string): string => {
    if (!translations) return fallback;
    const value = translations[key];
    return typeof value === 'string' ? value : fallback;
};

export default function AppLayout({
    user,
    header,
    children,
}: PropsWithChildren<AppLayoutProps>) {
    const { locale, translations } = usePage<AppPageProps>().props;
    const common = translations?.common as Record<string, string> | undefined;

    return (
        <div className="min-h-screen bg-gray-100 dark:bg-gray-900 transition-colors">
            <nav className="bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div className="flex justify-between h-16">
                        <div className="flex">
                            <div className="shrink-0 flex items-center">
                                <Link href={route('landing')}>
                                    <img
                                        src="/images/logo-black.png"
                                        alt="Expenses"
                                        className="h-8 w-auto block dark:hidden"
                                    />
                                    <img
                                        src="/images/logo-white.png"
                                        alt="Expenses"
                                        className="h-8 w-auto hidden dark:block"
                                    />
                                </Link>
                            </div>
                            <div className="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
                                <Link
                                    href={route('dashboard')}
                                    className="inline-flex items-center px-1 pt-1 text-sm font-medium text-gray-900 dark:text-gray-100"
                                >
                                    {t(common, 'dashboard', 'Dashboard')}
                                </Link>
                                <Link
                                    href={route('planning.index')}
                                    className="inline-flex items-center px-1 pt-1 text-sm font-medium text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200"
                                >
                                    {t(common, 'planning', 'Planning')}
                                </Link>
                                <Link
                                    href={route('expenses.index')}
                                    className="inline-flex items-center px-1 pt-1 text-sm font-medium text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200"
                                >
                                    {t(common, 'expenses', 'Expenses')}
                                </Link>
                            </div>
                        </div>
                        <div className="hidden sm:flex sm:items-center sm:ml-6 gap-4">
                            {/* Language Switcher */}
                            <div className="flex items-center gap-1">
                                <a
                                    href={route('language', { lang: 'en' })}
                                    className={`px-2 py-1 text-xs rounded ${locale === 'en'
                                        ? 'bg-indigo-600 text-white'
                                        : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600'
                                        }`}
                                >
                                    EN
                                </a>
                                <a
                                    href={route('language', { lang: 'my' })}
                                    className={`px-2 py-1 text-xs rounded ${locale === 'my'
                                        ? 'bg-indigo-600 text-white'
                                        : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600'
                                        }`}
                                >
                                    MY
                                </a>
                            </div>

                            {/* Theme Toggle */}
                            <ThemeToggle
                                className="p-2 rounded-md text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700"
                            />

                            {/* User Menu */}
                            <div className="flex items-center">
                                <Link
                                    href={route('profile.edit')}
                                    className="inline-flex items-center text-sm font-medium text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 mr-4"
                                >
                                    {user.name}
                                </Link>
                                <Link
                                    href={route('logout')}
                                    method="post"
                                    as="button"
                                    className="text-sm font-medium text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200"
                                >
                                    {t(common, 'logout', 'Logout')}
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </nav>

            {header && (
                <header className="bg-white dark:bg-gray-800 shadow">
                    <div className="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8 text-gray-900 dark:text-gray-100">
                        {header}
                    </div>
                </header>
            )}

            <main className="text-gray-900 dark:text-gray-100">{children}</main>
        </div>
    );
}
