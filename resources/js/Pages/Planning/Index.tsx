import { Head, Link } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';
import { PageProps, Planning } from '@/types';
import { formatMYR } from '@/utils/money';

interface PlanningIndexProps extends PageProps {
    plannings: Planning[];
}

export default function Index({ auth, plannings }: PlanningIndexProps) {
    return (
        <AppLayout user={auth.user!} header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">Planning</h2>}>
            <Head title="Planning" />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div className="mb-6 flex justify-end">
                        <Link
                            href="/planning/create"
                            className="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700"
                        >
                            New Planning
                        </Link>
                    </div>

                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div className="p-6">
                            {(!plannings || plannings.length === 0) ? (
                                <p className="text-gray-500 text-center py-8">
                                    No plannings yet. Create your first one!
                                </p>
                            ) : (
                                <div className="grid gap-4">
                                    {plannings.map((planning) => (
                                        <Link
                                            key={planning.planning_id}
                                            href={`/planning/${planning.planning_id}`}
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
                                                        {formatMYR(planning.salary)}
                                                    </p>
                                                    <p className="text-sm text-gray-400">
                                                        Planned spending: {formatMYR(planning.spending)}
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
