import { PropsWithChildren, ReactNode, useState, useEffect } from 'react';
import { Link, usePage } from '@inertiajs/react';
import { User, PageProps } from '@/types';
import { route } from '@/utils/route';

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
    const [theme, setTheme] = useState<'light' | 'dark'>('light');

    // Load theme from localStorage on mount
    useEffect(() => {
        const savedTheme = localStorage.getItem('theme') as 'light' | 'dark' | null;
        if (savedTheme) {
            setTheme(savedTheme);
            document.documentElement.classList.toggle('dark', savedTheme === 'dark');
        } else if (window.matchMedia('(prefers-color-scheme: dark)').matches) {
            setTheme('dark');
            document.documentElement.classList.add('dark');
        }
    }, []);

    const toggleTheme = () => {
        const newTheme = theme === 'light' ? 'dark' : 'light';
        setTheme(newTheme);
        localStorage.setItem('theme', newTheme);
        document.documentElement.classList.toggle('dark', newTheme === 'dark');
    };

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
                            <button
                                onClick={toggleTheme}
                                className="p-2 rounded-md text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700"
                                aria-label="Toggle theme"
                            >
                                {theme === 'light' ? (
                                    <svg className="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                                    </svg>
                                ) : (
                                    <svg className="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                                    </svg>
                                )}
                            </button>

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
