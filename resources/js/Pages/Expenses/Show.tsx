import { Head, Link } from '@inertiajs/react';
import PlanningSummary from '@/Components/Financial/PlanningSummary';
import PageContainer from '@/Components/UI/PageContainer';
import AppLayout from '@/Layouts/AppLayout';
import { PageProps, Planning } from '@/types';

interface ShowProps extends PageProps { planning: Planning; }

export default function Show({ auth, planning }: ShowProps) {
    return <AppLayout user={auth.user!} header={<h1 className="text-xl font-bold">Expense projection</h1>}>
        <Head title={`Expense projection - ${planning.name}`} />
        <PageContainer className="py-8 sm:py-10">
            <div className="mb-7"><p className="text-sm font-semibold text-secondary">{planning.name}</p><h2 className="mt-1 text-2xl font-bold">Planned allocation breakdown</h2><p className="mt-2 max-w-3xl text-sm text-secondary">This read-only projection uses the monthly plan. It is not a transaction ledger and does not represent actual bank spending.</p></div>
            <PlanningSummary planning={planning} />
            <div className="mt-8 flex flex-wrap gap-3"><Link href={`/planning/${planning.planning_id}`} className="button-primary">Open full plan</Link><Link href="/expenses" className="button-secondary">Back to Expenses</Link></div>
        </PageContainer>
    </AppLayout>;
}
