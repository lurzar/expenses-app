import AppLayout from '@/Layouts/AppLayout';
import PageContainer from '@/Components/UI/PageContainer';
import StatePanel from '@/Components/UI/StatePanel';
import Surface from '@/Components/UI/Surface';
import { PageProps } from '@/types';
import { Head, Link, usePage } from '@inertiajs/react';

export interface AdminNavigationItem {
    key: string;
    label: string;
    description: string;
    href: string;
}

export interface AdminIndexProps extends PageProps {
    admin: {
        navigation: AdminNavigationItem[];
    };
}

function t(translations: Record<string, unknown> | undefined, key: string, fallback: string): string {
    const value = translations?.[key];
    return typeof value === 'string' ? value : fallback;
}

export default function Index({ auth, admin }: AdminIndexProps) {
    const { translations } = usePage<PageProps>().props;
    const dictionary = translations?.admin;
    const managementTools = admin.navigation.filter((item) => item.key !== 'overview');
    const title = t(dictionary, 'title', 'Admin control plane');

    return <AppLayout user={auth.user!} header={<div>
        <p className="text-sm font-semibold text-secondary">{t(dictionary, 'eyebrow', 'System administration')}</p>
        <h1 className="mt-1 text-2xl font-bold">{title}</h1>
    </div>}>
        <Head title={title} />
        <PageContainer className="space-y-6 py-8">
            <Surface className="ui-surface-raised p-6 md:p-8">
                <p className="max-w-3xl text-secondary">{t(dictionary, 'description', 'Manage platform capabilities without accessing private planning data.')}</p>
                <div className="mt-6 border-t border-[var(--app-border-quiet)] pt-5">
                    <h2 className="text-base font-bold">{t(dictionary, 'access_status', 'Access protected')}</h2>
                    <p className="mt-1 text-sm text-secondary">{t(dictionary, 'access_status_description', 'Laravel verifies your capability on every Admin request.')}</p>
                </div>
            </Surface>

            <nav aria-label={t(dictionary, 'navigation_label', 'Admin navigation')}>
                <ul className="grid gap-3 md:grid-cols-2 lg:grid-cols-3">
                    {admin.navigation.map((item) => <li key={item.key}>
                        <Link href={item.href} aria-current={item.key === 'overview' ? 'page' : undefined} className="app-admin-destination">
                            <span className="font-bold">{item.label}</span>
                            <span className="text-sm text-secondary">{item.description}</span>
                        </Link>
                    </li>)}
                </ul>
            </nav>

            <section aria-labelledby="admin-tools-heading">
                <h2 id="admin-tools-heading" className="mb-4 text-xl font-bold">{t(dictionary, 'available_tools', 'Available tools')}</h2>
                {managementTools.length === 0 ? <StatePanel
                    title={t(dictionary, 'no_tools_title', 'No management tools enabled')}
                    description={t(dictionary, 'no_tools_description', 'Additional tools will appear when their modules are delivered and you have access.')}
                /> : <div className="grid gap-4 md:grid-cols-2">
                    {managementTools.map((item) => <Surface key={item.key} className="p-5">
                        <Link href={item.href} className="font-bold">{item.label}</Link>
                        <p className="mt-2 text-sm text-secondary">{item.description}</p>
                    </Surface>)}
                </div>}
            </section>
        </PageContainer>
    </AppLayout>;
}
