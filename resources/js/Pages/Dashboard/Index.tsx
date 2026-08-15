import { Head } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';
import { PageProps, Planning } from '@/types';
import { formatMYR } from '@/utils/money';

interface DashboardProps extends PageProps {
    plannings: Planning[];
}

export default function Index({ auth, plannings }: DashboardProps) {
    return (
        <AppLayout user={auth.user!} header={<h2 className="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Dashboard</h2>}>
            <Head title="Dashboard" />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                        <div className="p-6 text-gray-900 dark:text-gray-100">
                            <h3 className="text-lg font-semibold mb-4">
                                Welcome back, {auth.user?.name}!
                            </h3>

                            <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div className="bg-indigo-50 dark:bg-indigo-900/30 rounded-lg p-6">
                                    <h4 className="text-sm font-medium text-indigo-600 dark:text-indigo-400 uppercase tracking-wide">
                                        Total Plannings
                                    </h4>
                                    <p className="mt-2 text-3xl font-bold text-indigo-900 dark:text-indigo-100">
                                        {plannings?.length || 0}
                                    </p>
                                </div>

                                <div className="bg-green-50 dark:bg-green-900/30 rounded-lg p-6">
                                    <h4 className="text-sm font-medium text-green-600 dark:text-green-400 uppercase tracking-wide">
                                        This Month's Budget
                                    </h4>
                                    <p className="mt-2 text-3xl font-bold text-green-900 dark:text-green-100">
                                        {plannings?.[0] ? formatMYR(plannings[0].salary) : 'RM —'}
                                    </p>
                                </div>

                                <div className="bg-purple-50 dark:bg-purple-900/30 rounded-lg p-6">
                                    <h4 className="text-sm font-medium text-purple-600 dark:text-purple-400 uppercase tracking-wide">
                                        Savings Goal
                                    </h4>
                                    <p className="mt-2 text-3xl font-bold text-purple-900 dark:text-purple-100">
                                        {plannings?.[0] ? formatMYR(plannings[0].totals.target_savings) : 'RM —'}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
