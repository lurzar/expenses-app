import { Head, Link } from '@inertiajs/react';
import DeletePlanDialog from '@/Components/Financial/DeletePlanDialog';
import PlanningSummary from '@/Components/Financial/PlanningSummary';
import PageContainer from '@/Components/UI/PageContainer';
import AppLayout from '@/Layouts/AppLayout';
import { PageProps, Planning } from '@/types';

interface ShowProps extends PageProps { planning: Planning; }

export default function Show({ auth, planning }: ShowProps) {
    return <AppLayout user={auth.user!} header={<h1 className="text-xl font-bold">{planning.name}</h1>}>
        <Head title={planning.name} />
        <PageContainer className="py-8 sm:py-10">
            <div className="mb-7"><p className="text-sm font-semibold text-secondary">Monthly plan</p><h2 className="mt-1 text-2xl font-bold">Financial planning summary</h2><p className="mt-2 text-sm text-secondary">Exact server-authoritative figures for {planning.name}.</p></div>
            <PlanningSummary planning={planning} />
            <div className="mt-8 flex flex-col gap-5 border-t pt-6 sm:flex-row sm:items-center sm:justify-between" style={{ borderColor: 'var(--app-border-quiet)' }}>
                <div className="flex flex-wrap gap-3"><Link href={`/expenses/${planning.planning_id}`} className="button-primary">View expense projection</Link><Link href="/planning" className="button-secondary">Back to Planning</Link></div>
                <DeletePlanDialog planningId={planning.planning_id} planningName={planning.name} />
            </div>
        </PageContainer>
    </AppLayout>;
}
