import { FormEvent } from 'react';
import { Head, Link, router, useForm, usePage } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';
import PageContainer from '@/Components/UI/PageContainer';
import Surface from '@/Components/UI/Surface';
import { PageProps } from '@/types';

interface Role {
    name: string;
    protected: boolean;
    permissions: string[];
    unknown_permissions: string[];
    assignment_count: number;
    updated_at: string | null;
}

function PermissionChoices({ selected, onChange, groups, dictionary }: {
    selected: string[];
    onChange: (permissions: string[]) => void;
    groups: PermissionGroup[];
    dictionary: Record<string, unknown> | undefined;
}) {
    return <div className="space-y-4">
        {groups.map((group) => <fieldset key={group.module} className="border-t border-[var(--app-border-quiet)] pt-3">
            <legend className="text-sm font-bold capitalize">{group.module}</legend>
            <div className="mt-2 space-y-2">
                {group.permissions.map((permission) => <label key={permission.name} className="flex items-start gap-3 text-sm">
                    <input
                        type="checkbox"
                        checked={selected.includes(permission.name)}
                        onChange={(event) => onChange(event.target.checked
                            ? [...selected, permission.name].sort()
                            : selected.filter((name) => name !== permission.name))}
                        className="mt-1 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                    />
                    <span><span className="block font-semibold">{permission.label}</span><span className="block text-secondary">{permission.description}</span></span>
                </label>)}
            </div>
        </fieldset>)}
    </div>;
}

function CustomRoleForm({ role, groups, dictionary }: { role?: Role; groups: PermissionGroup[]; dictionary: Record<string, unknown> | undefined }) {
    const isNew = role === undefined;
    const { data, setData, post, patch, processing, errors } = useForm({
        name: role?.name ?? '',
        permissions: role?.permissions ?? [],
        updated_at: role?.updated_at ?? '',
    });
    const submit = (event: FormEvent) => {
        event.preventDefault();
        if (isNew) {
            post('/admin/roles', { preserveScroll: true });
            return;
        }
        patch(`/admin/roles/${role.name}`, { preserveScroll: true });
    };

    return <form onSubmit={submit} className="space-y-4">
        <div>
            <label htmlFor={`role-name-${role?.name ?? 'new'}`} className="block text-sm font-semibold">{t(dictionary, 'role_name', 'Role name')}</label>
            <input id={`role-name-${role?.name ?? 'new'}`} value={data.name} onChange={(event) => setData('name', event.target.value)} className="app-input mt-2 w-full" required maxLength={50} />
            {errors.name && <p role="alert" className="mt-1 text-sm text-red-700 dark:text-red-300">{errors.name}</p>}
        </div>
        <PermissionChoices selected={data.permissions} onChange={(permissions) => setData('permissions', permissions)} groups={groups} dictionary={dictionary} />
        {(errors.permissions || errors.updated_at) && <p role="alert" className="text-sm text-red-700 dark:text-red-300">{errors.permissions ?? errors.updated_at}</p>}
        <button type="submit" disabled={processing} className="app-button app-button-primary disabled:opacity-60">{isNew ? t(dictionary, 'create_role', 'Create role') : t(dictionary, 'save_role', 'Save role')}</button>
    </form>;
}

interface PermissionGroup {
    module: string;
    permissions: Array<{ name: string; label: string; description: string }>;
}

interface Props extends PageProps {
    roles: Role[];
    permission_groups: PermissionGroup[];
}

function t(dictionary: Record<string, unknown> | undefined, key: string, fallback: string): string {
    const value = dictionary?.[key];

    return typeof value === 'string' ? value : fallback;
}

export default function Index({ auth, roles, permission_groups }: Props) {
    const dictionary = usePage<PageProps>().props.translations?.admin;
    const title = t(dictionary, 'roles_title', 'Roles and permissions');

    return <AppLayout user={auth.user!} header={<div>
        <p className="text-sm font-semibold text-secondary">{t(dictionary, 'roles_eyebrow', 'Authorization management')}</p>
        <h1 className="mt-1 text-2xl font-bold">{title}</h1>
    </div>}>
        <Head title={title} />
        <PageContainer className="space-y-6 py-8">
            <div>
                <Link href="/admin" className="text-sm font-semibold text-indigo-700 hover:underline dark:text-indigo-300">
                    ← {t(dictionary, 'back_to_admin', 'Back to Admin')}
                </Link>
                <p className="mt-3 max-w-3xl text-secondary">{t(dictionary, 'roles_intro', 'Protected roles are defined by code. Custom roles can use only approved capabilities.')}</p>
            </div>

            <Surface className="p-5 md:p-6">
                <h2 className="text-xl font-bold">{t(dictionary, 'create_role', 'Create role')}</h2>
                <div className="mt-5 max-w-3xl"><CustomRoleForm groups={permission_groups} dictionary={dictionary} /></div>
            </Surface>

            <div className="space-y-4">
                {roles.map((role) => <Surface key={role.name} className="p-5 md:p-6">
                    <div className="flex flex-wrap items-center justify-between gap-3">
                        <h2 className="text-xl font-bold">{role.name}{role.protected && <span className="ml-2 app-badge">{t(dictionary, 'protected_role', 'Protected role')}</span>}</h2>
                        <span className="text-sm text-secondary">{role.assignment_count} {t(dictionary, 'assignments', 'assignments')}</span>
                    </div>
                    {role.unknown_permissions.length > 0 && <p role="alert" className="mt-3 text-sm text-amber-700 dark:text-amber-300">{t(dictionary, 'unknown_permissions', 'Unknown permissions require operator review:')} {role.unknown_permissions.join(', ')}</p>}
                    {role.protected ? <p className="mt-3 text-sm text-secondary">{role.permissions.join(', ')}</p> : <div className="mt-5 max-w-3xl">
                        <CustomRoleForm role={role} groups={permission_groups} dictionary={dictionary} />
                        <button type="button" onClick={() => {
                            if (window.confirm(t(dictionary, 'confirm_retire_role', 'Retire this unassigned custom role?'))) {
                                router.delete(`/admin/roles/${role.name}`, { preserveScroll: true });
                            }
                        }} className="app-button mt-4 border border-red-700 text-red-700 hover:bg-red-50 dark:text-red-300">
                            {t(dictionary, 'retire_role', 'Retire role')}
                        </button>
                    </div>}
                </Surface>)}
            </div>

            <section aria-labelledby="permission-catalog-heading">
                <h2 id="permission-catalog-heading" className="mb-4 text-xl font-bold">{t(dictionary, 'permission_catalog', 'Approved capability catalog')}</h2>
                <div className="grid gap-4 md:grid-cols-2">
                    {permission_groups.map((group) => <Surface key={group.module} className="p-5">
                        <h3 className="font-bold capitalize">{group.module}</h3>
                        <ul className="mt-3 space-y-3">
                            {group.permissions.map((permission) => <li key={permission.name}>
                                <p className="text-sm font-semibold">{permission.label}</p>
                                <p className="text-sm text-secondary">{permission.description}</p>
                            </li>)}
                        </ul>
                    </Surface>)}
                </div>
            </section>
        </PageContainer>
    </AppLayout>;
}
