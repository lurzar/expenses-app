import { Head, Link } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';
import { PageProps, Planning } from '@/types';
import { formatMYR } from '@/utils/money';

interface ShowProps extends PageProps {
    planning: Planning;
}

export default function Show({ auth, planning }: ShowProps) {
    const planningName = planning.name;

    return (
        <AppLayout user={auth.user!} header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Expenses - {planningName}</h2>}>
            <Head title={`Expenses - ${planningName}`} />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div className="p-6">
                            <div className="mb-6">
                                <h3 className="text-lg font-semibold text-gray-800">
                                    {planningName}
                                </h3>
                                <p className="text-sm text-gray-500">
                                    {planning.name}
                                </p>
                            </div>

                            <div className="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
                                <div className="bg-blue-50 rounded-lg p-4">
                                    <p className="text-sm text-blue-600">Salary</p>
                                    <p className="text-2xl font-bold text-blue-900">
                                        {formatMYR(planning.salary)}
                                    </p>
                                </div>
                                <div className="bg-red-50 rounded-lg p-4">
                                    <p className="text-sm text-red-600">Total Spending</p>
                                    <p className="text-2xl font-bold text-red-900">
                                        {formatMYR(planning.totals.spending)}
                                    </p>
                                </div>
                                <div className="bg-green-50 rounded-lg p-4">
                                    <p className="text-sm text-green-600">Remaining</p>
                                    <p className="text-2xl font-bold text-green-900">
                                        {formatMYR(planning.totals.balance)}
                                    </p>
                                </div>
                            </div>

                            <div className="border-t pt-6">
                                <p className="text-gray-500 text-center py-8">
                                    No expenses recorded yet for this planning.
                                </p>
                            </div>

                            <div className="mt-8">
                                <Link
                                    href="/expenses"
                                    className="text-indigo-600 hover:text-indigo-500"
                                >
                                    ← Back to Expenses
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
