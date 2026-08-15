import { PropsWithChildren } from 'react';
import { Link, usePage } from '@inertiajs/react';
import { PageProps } from '@/types';
import { route } from '@/utils/route';
import ThemeToggle from '@/Components/ThemeToggle';

interface GuestPageProps extends PageProps {
    locale?: string;
}

export default function GuestLayout({ children }: PropsWithChildren) {
    const { locale } = usePage<GuestPageProps>().props;

    return (
        <div className="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100 dark:bg-gray-900 transition-colors">
            {/* Top right controls */}
            <div className="absolute top-4 right-4 flex items-center gap-4">
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
                    className="p-2 rounded-md text-gray-500 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-700"
                />
            </div>

            <div>
                <Link href={route('landing')}>
                    <img
                        src="/images/logo-black.png"
                        alt="Expenses"
                        className="h-12 w-auto block dark:hidden"
                    />
                    <img
                        src="/images/logo-white.png"
                        alt="Expenses"
                        className="h-12 w-auto hidden dark:block"
                    />
                </Link>
            </div>

            <div className="w-full sm:max-w-md mt-6 px-6 py-4 bg-white dark:bg-gray-800 shadow-md overflow-hidden sm:rounded-lg">
                <div className="text-gray-900 dark:text-gray-100">
                    {children}
                </div>
            </div>
        </div>
    );
}
