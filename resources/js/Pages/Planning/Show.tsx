import { Head, Link, router } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';
import { PageProps, Planning, SectionItem } from '@/types';

interface ShowProps extends PageProps {
    planning: Planning;
}

export default function Show({ auth, planning }: ShowProps) {
    const handleDelete = () => {
        if (confirm('Are you sure you want to delete this planning?')) {
            router.delete(`/planning/${planning.planning_id}`);
        }
    };

    const calculateSectionTotal = (items?: SectionItem[]) => {
        if (!items || !Array.isArray(items)) return 0;
        return items.reduce((sum, item) => sum + (parseFloat(item.amount) || 0), 0);
    };

    const renderSection = (title: string, items?: SectionItem[], colorClass = 'bg-gray-50') => {
        if (!items || items.length === 0) return null;

        return (
            <div className={`rounded-lg p-4 ${colorClass}`}>
                <div className="flex justify-between items-center mb-3">
                    <h4 className="font-semibold">{title}</h4>
                    <span className="text-sm font-medium">
                        RM {calculateSectionTotal(items).toLocaleString()}
                    </span>
                </div>
                <div className="space-y-2">
                    {items.map((item, index) => (
                        <div key={index} className="flex justify-between text-sm">
                            <span>{item.item}</span>
                            <span>RM {parseFloat(item.amount || '0').toLocaleString()}</span>
                        </div>
                    ))}
                </div>
            </div>
        );
    };

    const totalSpending =
        calculateSectionTotal(planning.sections?.savings) +
        calculateSectionTotal(planning.sections?.commitments) +
        calculateSectionTotal(planning.sections?.others);

    return (
        <AppLayout user={auth.user!} header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">{planning.name || `${planning.month}, ${planning.year}`}</h2>}>
            <Head title={planning.name || 'Planning'} />

            <div className="py-12">
                <div className="max-w-4xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div className="p-6">
                            {/* Actions */}
                            <div className="mb-6 flex justify-between items-center">
                                <p className="text-sm text-gray-500">
                                    {planning.month} {planning.year}
                                </p>
                                <div className="flex gap-2">
                                    <Link
                                        href={`/expenses/${planning.planning_id}`}
                                        className="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700"
                                    >
                                        View Expenses
                                    </Link>
                                    <button
                                        onClick={handleDelete}
                                        className="bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700"
                                    >
                                        Delete
                                    </button>
                                </div>
                            </div>

                            {/* Summary Cards */}
                            <div className="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                                <div className="bg-blue-50 rounded-lg p-4 text-center">
                                    <p className="text-sm text-blue-600">Salary</p>
                                    <p className="text-2xl font-bold text-blue-900">
                                        RM {planning.salary?.toLocaleString() || '0'}
                                    </p>
                                </div>
                                <div className="bg-red-50 rounded-lg p-4 text-center">
                                    <p className="text-sm text-red-600">Total Spending</p>
                                    <p className="text-2xl font-bold text-red-900">
                                        RM {totalSpending.toLocaleString()}
                                    </p>
                                </div>
                                <div className="bg-green-50 rounded-lg p-4 text-center">
                                    <p className="text-sm text-green-600">Remaining</p>
                                    <p className="text-2xl font-bold text-green-900">
                                        RM {((planning.salary || 0) - totalSpending).toLocaleString()}
                                    </p>
                                </div>
                                <div className="bg-purple-50 rounded-lg p-4 text-center">
                                    <p className="text-sm text-purple-600">Savings</p>
                                    <p className="text-2xl font-bold text-purple-900">
                                        RM {calculateSectionTotal(planning.sections?.savings).toLocaleString()}
                                    </p>
                                </div>
                            </div>

                            {/* Sections */}
                            <div className="space-y-4">
                                {renderSection('Savings', planning.sections?.savings, 'bg-green-50')}
                                {renderSection('Commitments', planning.sections?.commitments, 'bg-blue-50')}
                                {renderSection('Others', planning.sections?.others, 'bg-gray-50')}
                            </div>

                            <div className="mt-8">
                                <Link
                                    href="/planning"
                                    className="text-indigo-600 hover:text-indigo-500"
                                >
                                    ← Back to Planning
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
