import { Head, Link } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';
import { PageProps, Planning } from '@/types';

interface ExpensesIndexProps extends PageProps {
    plannings: Planning[];
}

export default function Index({ auth, plannings }: ExpensesIndexProps) {
    return (
        <AppLayout user={auth.user!} header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Expenses</h2>}>
            <Head title="Expenses" />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div className="p-6">
                            <p className="text-gray-600 mb-6">
                                Select a planning to view and manage expenses.
                            </p>

                            {(!plannings || plannings.length === 0) ? (
                                <p className="text-gray-500 text-center py-8">
                                    No plannings found. Create a planning first.
                                </p>
                            ) : (
                                <div className="grid gap-4">
                                    {plannings.map((planning) => (
                                        <Link
                                            key={planning.planning_id}
                                            href={`/expenses/${planning.planning_id}`}
                                            className="block p-4 border rounded-lg hover:bg-gray-50 transition"
                                        >
                                            <div className="flex justify-between items-center">
                                                <div>
                                                    <h3 className="font-semibold text-lg">
                                                        {planning.name || `${planning.month}, ${planning.year}`}
                                                    </h3>
                                                    <p className="text-sm text-gray-500">
                                                        {planning.month} {planning.year}
                                                    </p>
                                                </div>
                                                <div className="text-right">
                                                    <p className="text-sm text-gray-500">Salary</p>
                                                    <p className="font-semibold text-indigo-600">
                                                        RM {planning.salary?.toLocaleString() || '0'}
                                                    </p>
                                                    <p className="text-sm text-gray-400">
                                                        Spending: {planning.spending || 'RM 0'}
                                                    </p>
                                                </div>
                                            </div>
                                        </Link>
                                    ))}
                                </div>
                            )}
                        </div>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
