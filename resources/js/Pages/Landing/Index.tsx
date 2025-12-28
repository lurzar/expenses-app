import { Head, Link, usePage } from '@inertiajs/react';
import { PageProps } from '@/types';
import { route } from '@/utils/route';

// Helper function to safely get translation
const t = (translations: Record<string, unknown> | undefined, key: string, fallback: string): string => {
    if (!translations) return fallback;
    const value = translations[key];
    return typeof value === 'string' ? value : fallback;
};

export default function Index({ auth }: PageProps) {
    const { translations } = usePage<PageProps>().props;
    const common = translations?.common as Record<string, string> | undefined;

    return (
        <>
            <Head title="Welcome" />
            <div className="min-h-screen bg-gradient-to-br from-indigo-500 via-purple-500 to-pink-500">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <nav className="flex items-center justify-between py-6">
                        <img
                            src="/images/logo-white.png"
                            alt="Expenses"
                            className="h-8 w-auto"
                        />
                        <div className="flex items-center space-x-4">
                            {auth.user ? (
                                <Link
                                    href={route('dashboard')}
                                    className="text-white hover:text-gray-200 font-medium"
                                >
                                    {t(common, 'dashboard', 'Dashboard')}
                                </Link>
                            ) : (
                                <>
                                    <Link
                                        href={route('login')}
                                        className="text-white hover:text-gray-200 font-medium"
                                    >
                                        {t(common, 'login', 'Login')}
                                    </Link>
                                    <Link
                                        href={route('register')}
                                        className="bg-white text-indigo-600 px-4 py-2 rounded-lg font-medium hover:bg-gray-100"
                                    >
                                        {t(common, 'register', 'Register')}
                                    </Link>
                                </>
                            )}
                        </div>
                    </nav>

                    <div className="flex flex-col items-center justify-center min-h-[80vh] text-center">
                        <h1 className="text-5xl md:text-7xl font-bold text-white mb-6">
                            Track Your Expenses
                        </h1>
                        <p className="text-xl text-white/80 mb-8 max-w-2xl">
                            Plan your budget, track your spending, and achieve
                            your financial goals with our simple expense tracker.
                        </p>
                        <Link
                            href={auth.user ? route('dashboard') : route('register')}
                            className="bg-white text-indigo-600 px-8 py-4 rounded-xl font-semibold text-lg hover:bg-gray-100 transition"
                        >
                            Get Started
                        </Link>
                    </div>
                </div>
            </div>
        </>
    );
}
