import { FormEventHandler, useMemo } from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';
import { PageProps } from '@/types';

interface SectionItem {
    item: string;
    amount: string;
}

export default function Create({ auth }: PageProps) {
    const { data, setData, post, processing, errors, transform } = useForm({
        month: getCurrentMonthName(),
        year: new Date().getFullYear().toString(),
        salary: '',
        saving_rate: '20',
        savings_values: [{ item: '', amount: '' }] as SectionItem[],
        commitments_values: [{ item: '', amount: '' }] as SectionItem[],
        others_values: [{ item: '', amount: '' }] as SectionItem[],
        totals: {} as Record<string, number>,
    });

    // Calculate totals with cascading balances (matching Livewire logic exactly)
    const calculations = useMemo(() => {
        const salary = parseFloat(data.salary) || 0;
        const savingRate = parseFloat(data.saving_rate) || 0;

        // Saving Section: total_savings based on saving rate, balance_after_saving_rates
        const totalSavings = (salary * savingRate) / 100;
        const balanceAfterSavingRates = salary - totalSavings;

        // Sum of savings items (for display, but not used in cascade)
        const savingsItemsTotal = data.savings_values.reduce(
            (sum, item) => sum + (parseFloat(item.amount) || 0), 0
        );

        // Commitment Section: total_commitment, balance_after_savings
        const balanceAfterSavings = salary - savingsItemsTotal;
        const commitmentsTotal = data.commitments_values.reduce(
            (sum, item) => sum + (parseFloat(item.amount) || 0), 0
        );

        // Other Section: total_other, balance_after_commitments
        const balanceAfterCommitments = balanceAfterSavings - commitmentsTotal;
        const othersTotal = data.others_values.reduce(
            (sum, item) => sum + (parseFloat(item.amount) || 0), 0
        );

        // Final balance
        const totalBalance = balanceAfterCommitments - othersTotal;

        return {
            totalSavings,
            balanceAfterSavingRates,
            savingsItemsTotal,
            balanceAfterSavings,
            commitmentsTotal,
            balanceAfterCommitments,
            othersTotal,
            totalBalance,
        };
    }, [data.salary, data.saving_rate, data.savings_values, data.commitments_values, data.others_values]);

    const addItem = (section: 'savings_values' | 'commitments_values' | 'others_values') => {
        setData(section, [...data[section], { item: '', amount: '' }]);
    };

    const removeItem = (section: 'savings_values' | 'commitments_values' | 'others_values', index: number) => {
        if (data[section].length > 1) {
            setData(section, data[section].filter((_, i) => i !== index));
        }
    };

    const updateItem = (
        section: 'savings_values' | 'commitments_values' | 'others_values',
        index: number,
        field: 'item' | 'amount',
        value: string
    ) => {
        const newItems = [...data[section]];
        newItems[index] = { ...newItems[index], [field]: value };
        setData(section, newItems);
    };

    const submit: FormEventHandler = (e) => {
        e.preventDefault();

        // Build totals object matching Livewire structure
        const totals = {
            saving: calculations.totalSavings,
            balance: calculations.totalBalance,
            commitment: calculations.commitmentsTotal,
            other: calculations.othersTotal,
        };

        // Use transform to include totals in the submission
        transform((formData) => ({
            ...formData,
            totals,
        }));

        post('/planning');
    };

    return (
        <AppLayout user={auth.user!} header={<h2 className="font-semibold text-xl text-gray-800 leading-tight">New Planning</h2>}>
            <Head title="New Planning" />

            <div className="py-12">
                <div className="max-w-4xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <form onSubmit={submit} className="p-6 space-y-6">

                            {/* Salary Information */}
                            <div className="border rounded-lg p-4 bg-blue-50">
                                <h3 className="font-semibold text-lg mb-4">💰 Salary Information</h3>
                                <div className="grid grid-cols-2 md:grid-cols-4 gap-4">
                                    <div>
                                        <label htmlFor="current_month" className="block text-sm font-medium text-gray-700">Month</label>
                                        <input
                                            id="current_month"
                                            name="current_month"
                                            type="text"
                                            value={data.month}
                                            disabled
                                            className="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm"
                                        />
                                        <input type="hidden" name="month" value={data.month} />
                                    </div>

                                    <div>
                                        <label htmlFor="current_year" className="block text-sm font-medium text-gray-700">Year</label>
                                        <input
                                            id="current_year"
                                            name="current_year"
                                            type="text"
                                            value={data.year}
                                            disabled
                                            className="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm"
                                        />
                                        <input type="hidden" name="year" value={data.year} />
                                    </div>

                                    <div>
                                        <label htmlFor="salary" className="block text-sm font-medium text-gray-700">Salary (RM)</label>
                                        <input
                                            id="salary"
                                            name="salary"
                                            type="number"
                                            value={data.salary}
                                            onChange={(e) => setData('salary', e.target.value)}
                                            min="0"
                                            step="any"
                                            placeholder="0.00"
                                            className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                            required
                                        />
                                        {errors.salary && <p className="mt-1 text-sm text-red-600">{errors.salary}</p>}
                                    </div>

                                    <div>
                                        <label htmlFor="saving_rate" className="block text-sm font-medium text-gray-700">Saving Rate (%)</label>
                                        <input
                                            id="saving_rate"
                                            name="saving_rate"
                                            type="number"
                                            value={data.saving_rate}
                                            onChange={(e) => setData('saving_rate', e.target.value)}
                                            min="20"
                                            max="100"
                                            step="5"
                                            className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                            required
                                        />
                                        {errors.saving_rate && <p className="mt-1 text-sm text-red-600">{errors.saving_rate}</p>}
                                    </div>
                                </div>
                            </div>

                            {/* Savings Section - shows total_savings (from rate) and balance_after_saving_rates */}
                            <div className="border rounded-lg p-4 bg-green-50">
                                <div className="flex justify-between items-center mb-4">
                                    <h3 className="font-semibold text-lg">🏦 Savings</h3>
                                    <div className="text-right text-sm">
                                        <div>Target Savings: <span className="font-medium text-green-700">RM {calculations.totalSavings.toLocaleString()}</span></div>
                                        <div className="text-gray-500">Balance After Rate: RM {calculations.balanceAfterSavingRates.toLocaleString()}</div>
                                    </div>
                                </div>

                                {data.savings_values.map((item, index) => (
                                    <div key={index} className="grid grid-cols-6 gap-2 mb-2">
                                        <div className="col-span-4">
                                            <input
                                                id={`savings_values_${index}_item`}
                                                name={`savings_values[${index}][item]`}
                                                type="text"
                                                placeholder="e.g. Emergency Fund, Investment"
                                                value={item.item}
                                                onChange={(e) => updateItem('savings_values', index, 'item', e.target.value)}
                                                className="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                            />
                                        </div>
                                        <div className="col-span-1">
                                            <input
                                                id={`savings_values_${index}_amount`}
                                                name={`savings_values[${index}][amount]`}
                                                type="number"
                                                placeholder="0.00"
                                                min="1"
                                                step="any"
                                                value={item.amount}
                                                onChange={(e) => updateItem('savings_values', index, 'amount', e.target.value)}
                                                className="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                            />
                                        </div>
                                        <div className="col-span-1 flex items-center">
                                            {index > 0 && (
                                                <button
                                                    type="button"
                                                    onClick={() => removeItem('savings_values', index)}
                                                    className="px-3 py-2 text-sm text-red-600 hover:text-red-800 bg-red-50 rounded-md"
                                                >
                                                    Remove
                                                </button>
                                            )}
                                        </div>
                                    </div>
                                ))}

                                <button
                                    type="button"
                                    onClick={() => addItem('savings_values')}
                                    className="mt-2 text-sm text-indigo-600 hover:text-indigo-800 bg-indigo-50 px-3 py-1 rounded-md"
                                >
                                    + Add
                                </button>
                            </div>

                            {/* Commitments Section - shows total_commitment and balance_after_savings */}
                            <div className="border rounded-lg p-4 bg-yellow-50">
                                <div className="flex justify-between items-center mb-4">
                                    <h3 className="font-semibold text-lg">📋 Commitments</h3>
                                    <div className="text-right text-sm">
                                        <div>Total: <span className="font-medium text-yellow-700">RM {calculations.commitmentsTotal.toLocaleString()}</span></div>
                                        <div className="text-gray-500">Balance After Savings: RM {calculations.balanceAfterSavings.toLocaleString()}</div>
                                    </div>
                                </div>

                                {data.commitments_values.map((item, index) => (
                                    <div key={index} className="grid grid-cols-6 gap-2 mb-2">
                                        <div className="col-span-4">
                                            <input
                                                id={`commitments_values_${index}_item`}
                                                name={`commitments_values[${index}][item]`}
                                                type="text"
                                                placeholder="e.g. Rent, Car loan, Insurance"
                                                value={item.item}
                                                onChange={(e) => updateItem('commitments_values', index, 'item', e.target.value)}
                                                className="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                            />
                                        </div>
                                        <div className="col-span-1">
                                            <input
                                                id={`commitments_values_${index}_amount`}
                                                name={`commitments_values[${index}][amount]`}
                                                type="number"
                                                placeholder="0.00"
                                                min="1"
                                                step="any"
                                                value={item.amount}
                                                onChange={(e) => updateItem('commitments_values', index, 'amount', e.target.value)}
                                                className="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                            />
                                        </div>
                                        <div className="col-span-1 flex items-center">
                                            {index > 0 && (
                                                <button
                                                    type="button"
                                                    onClick={() => removeItem('commitments_values', index)}
                                                    className="px-3 py-2 text-sm text-red-600 hover:text-red-800 bg-red-50 rounded-md"
                                                >
                                                    Remove
                                                </button>
                                            )}
                                        </div>
                                    </div>
                                ))}

                                <button
                                    type="button"
                                    onClick={() => addItem('commitments_values')}
                                    className="mt-2 text-sm text-indigo-600 hover:text-indigo-800 bg-indigo-50 px-3 py-1 rounded-md"
                                >
                                    + Add
                                </button>
                            </div>

                            {/* Others Section - shows total_other and balance_after_commitments */}
                            <div className="border rounded-lg p-4 bg-gray-50">
                                <div className="flex justify-between items-center mb-4">
                                    <h3 className="font-semibold text-lg">📦 Others</h3>
                                    <div className="text-right text-sm">
                                        <div>Total: <span className="font-medium text-gray-700">RM {calculations.othersTotal.toLocaleString()}</span></div>
                                        <div className="text-gray-500">Balance After Commitments: RM {calculations.balanceAfterCommitments.toLocaleString()}</div>
                                    </div>
                                </div>

                                {data.others_values.map((item, index) => (
                                    <div key={index} className="grid grid-cols-6 gap-2 mb-2">
                                        <div className="col-span-4">
                                            <input
                                                id={`others_values_${index}_item`}
                                                name={`others_values[${index}][item]`}
                                                type="text"
                                                placeholder="e.g. Groceries, Entertainment"
                                                value={item.item}
                                                onChange={(e) => updateItem('others_values', index, 'item', e.target.value)}
                                                className="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                            />
                                        </div>
                                        <div className="col-span-1">
                                            <input
                                                id={`others_values_${index}_amount`}
                                                name={`others_values[${index}][amount]`}
                                                type="number"
                                                placeholder="0.00"
                                                min="1"
                                                step="any"
                                                value={item.amount}
                                                onChange={(e) => updateItem('others_values', index, 'amount', e.target.value)}
                                                className="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                            />
                                        </div>
                                        <div className="col-span-1 flex items-center">
                                            {index > 0 && (
                                                <button
                                                    type="button"
                                                    onClick={() => removeItem('others_values', index)}
                                                    className="px-3 py-2 text-sm text-red-600 hover:text-red-800 bg-red-50 rounded-md"
                                                >
                                                    Remove
                                                </button>
                                            )}
                                        </div>
                                    </div>
                                ))}

                                <button
                                    type="button"
                                    onClick={() => addItem('others_values')}
                                    className="mt-2 text-sm text-indigo-600 hover:text-indigo-800 bg-indigo-50 px-3 py-1 rounded-md"
                                >
                                    + Add
                                </button>
                            </div>

                            {/* Summary */}
                            <div className="border rounded-lg p-4 bg-indigo-50">
                                <h3 className="font-semibold text-lg mb-4">📊 Planning Summary</h3>
                                <div className="grid grid-cols-2 gap-4">
                                    <div>
                                        <label htmlFor="total_savings" className="block text-sm font-medium text-gray-700">Total Savings (from rate)</label>
                                        <input
                                            id="total_savings"
                                            name="total_savings"
                                            type="text"
                                            value={`RM ${calculations.totalSavings.toLocaleString()}`}
                                            disabled
                                            className="mt-1 block w-full rounded-md border-gray-300 bg-gray-100 shadow-sm"
                                        />
                                    </div>
                                    <div>
                                        <label htmlFor="total_balance" className="block text-sm font-medium text-gray-700">Total Balance</label>
                                        <input
                                            id="total_balance"
                                            name="total_balance"
                                            type="text"
                                            value={`RM ${calculations.totalBalance.toLocaleString()}`}
                                            disabled
                                            className={`mt-1 block w-full rounded-md border-gray-300 shadow-sm ${calculations.totalBalance < 0 ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700'
                                                }`}
                                        />
                                    </div>
                                </div>
                            </div>

                            <div className="flex justify-end gap-4">
                                <Link
                                    href="/planning"
                                    className="px-4 py-2 text-gray-700 hover:text-gray-900"
                                >
                                    Cancel
                                </Link>
                                <button
                                    type="submit"
                                    disabled={processing}
                                    className="bg-indigo-600 text-white px-6 py-2 rounded-md hover:bg-indigo-700 disabled:opacity-50"
                                >
                                    Create Planning
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}

function getCurrentMonthName(): string {
    const months = [
        'January', 'February', 'March', 'April', 'May', 'June',
        'July', 'August', 'September', 'October', 'November', 'December'
    ];
    return months[new Date().getMonth()];
}
