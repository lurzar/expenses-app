import { Head, Link } from '@inertiajs/react';
import PlanningSummary from '@/Components/Financial/PlanningSummary';
import PlanningTrend from '@/Components/Financial/PlanningTrend';
import PageContainer from '@/Components/UI/PageContainer';
import StatePanel from '@/Components/UI/StatePanel';
import AppLayout from '@/Layouts/AppLayout';
import { PageProps, Planning } from '@/types';

interface DashboardProps extends PageProps { plannings: Planning[]; }

export default function Index({ auth, plannings }: DashboardProps) {
    const selected = plannings?.[0];

    return <AppLayout user={auth.user!} header={<h1 className="text-xl font-bold">Dashboard</h1>}>
        <Head title="Dashboard" />
        <PageContainer className="py-8 sm:py-10">
            {!selected ? <StatePanel title="Create your first monthly plan" description="Add income and planned allocations to unlock exact figures and useful comparisons." action={<Link href="/planning/create" className="button-primary">Create a plan</Link>} /> : <div className="space-y-8">
                <div><p className="text-sm font-semibold text-secondary">Welcome back, {auth.user?.name}</p><h2 className="mt-1 text-2xl font-bold">Selected plan: {selected.name}</h2><p className="mt-2 text-sm text-secondary">The latest available record is selected explicitly; figures below remain planned values.</p></div>
                <PlanningSummary planning={selected} showSections={false} />
                <PlanningTrend plannings={plannings} selectedId={selected.planning_id} />
                <div className="flex flex-wrap gap-3"><Link href={`/planning/${selected.planning_id}`} className="button-primary">Open full plan</Link><Link href="/planning/create" className="button-secondary">Create another plan</Link></div>
            </div>}
        </PageContainer>
    </AppLayout>;
}
