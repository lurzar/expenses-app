import { FormEvent, useEffect, useState } from 'react';
import { Head, Link, router, useForm, usePage } from '@inertiajs/react';
import AppLayout from '@/Layouts/AppLayout';
import PageContainer from '@/Components/UI/PageContainer';
import StatePanel from '@/Components/UI/StatePanel';
import Surface from '@/Components/UI/Surface';
import { PageProps } from '@/types';

type AdministrativeRole = 'admin' | 'super-admin';

interface AdminUser {
    user_id: string;
    name: string;
    email: string;
    verified: boolean;
    roles: AdministrativeRole[];
    authorization_version: number;
}

interface RoleOption {
    value: AdministrativeRole;
    label: string;
    description: string;
    permissions: string[];
}

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

export interface AdminUsersIndexProps extends PageProps {
    users: {
        data: AdminUser[];
        links: PaginationLink[];
        current_page: number;
        last_page: number;
        from: number | null;
        to: number | null;
        total: number;
    };
    filters: {
        search: string;
        status: string;
        role: string;
    };
    capabilities: {
        manage_super_admin: boolean;
    };
    role_options: RoleOption[];
}

function text(dictionary: Record<string, unknown> | undefined, key: string, fallback: string): string {
    const value = dictionary?.[key];
    return typeof value === 'string' ? value : fallback;
}

function paginationLabel(label: string): string {
    return label.replace('&laquo;', '‹').replace('&raquo;', '›');
}

function UserRoleForm({
    user,
    roleOptions,
    canManageSuperAdmin,
    dictionary,
    currentUserId,
}: {
    user: AdminUser;
    roleOptions: RoleOption[];
    canManageSuperAdmin: boolean;
    dictionary: Record<string, unknown> | undefined;
    currentUserId: string;
}) {
    const { data, setData, patch, processing, errors } = useForm({
        roles: user.roles,
        authorization_version: user.authorization_version,
    });
    const rolesSignature = user.roles.join(',');

    useEffect(() => {
        setData({
            roles: user.roles,
            authorization_version: user.authorization_version,
        });
    }, [rolesSignature, user.authorization_version]);

    const toggleRole = (role: AdministrativeRole, checked: boolean) => {
        setData('roles', checked
            ? [...data.roles, role].sort() as AdministrativeRole[]
            : data.roles.filter((currentRole) => currentRole !== role));
    };

    const submit = (event: FormEvent) => {
        event.preventDefault();

        if (! window.confirm(text(dictionary, 'confirm_roles', 'Apply these administrative role changes?'))) {
            return;
        }

        patch(`/admin/users/${user.user_id}/roles`, {
            preserveScroll: true,
        });
    };

    return <form onSubmit={submit} className="min-w-64 space-y-3">
        <fieldset>
            <legend className="sr-only">{text(dictionary, 'administrative_roles_for', `Administrative roles for ${user.name}`)}</legend>
            <div className="space-y-2">
                {roleOptions.map((role) => {
                    const checked = data.roles.includes(role.value);
                    const protectedRole = role.value === 'super-admin';
                    const removingOwnRole = currentUserId === user.user_id && user.roles.includes(role.value);
                    const disabled = processing || (protectedRole && ! canManageSuperAdmin) || removingOwnRole;

                    return <label key={role.value} className="flex items-start gap-3 rounded-lg border border-[var(--app-border-quiet)] p-3">
                        <input
                            type="checkbox"
                            checked={checked}
                            disabled={disabled}
                            onChange={(event) => toggleRole(role.value, event.target.checked)}
                            aria-label={`${role.label} for ${user.name}`}
                            className="mt-1 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                        />
                        <span>
                            <span className="block text-sm font-semibold">{role.label}</span>
                            <span className="block text-xs text-secondary">{role.description}</span>
                            <ul className="mt-1 flex flex-wrap gap-x-2 text-xs text-secondary">
                                {role.permissions.map((permission) => <li key={permission}>{permission}</li>)}
                            </ul>
                            {protectedRole && <span className="mt-1 block text-xs font-semibold text-amber-700 dark:text-amber-300">
                                {text(dictionary, 'protected_role', 'Protected role')}
                            </span>}
                        </span>
                    </label>;
                })}
            </div>
        </fieldset>

        {(errors.roles || errors.authorization_version) && <p role="alert" className="text-sm text-red-700 dark:text-red-300">
            {errors.roles ?? errors.authorization_version}
        </p>}

        <button
            type="submit"
            disabled={processing}
            aria-label={`${text(dictionary, 'save_roles', 'Save roles')} for ${user.name}`}
            className="app-button app-button-primary w-full disabled:cursor-not-allowed disabled:opacity-60"
        >
            {processing ? text(dictionary, 'saving', 'Saving…') : text(dictionary, 'save_roles', 'Save roles')}
        </button>
    </form>;
}

export default function Index({ auth, users, filters, capabilities, role_options }: AdminUsersIndexProps) {
    const { translations } = usePage<PageProps>().props;
    const dictionary = translations?.admin;
    const [filterState, setFilterState] = useState(filters);
    const title = text(dictionary, 'users_title', 'Users and roles');

    const submitFilters = (event: FormEvent) => {
        event.preventDefault();
        router.get('/admin/users', filterState, {
            preserveState: true,
            replace: true,
        });
    };

    return <AppLayout user={auth.user!} header={<div>
        <p className="text-sm font-semibold text-secondary">{text(dictionary, 'users_eyebrow', 'User administration')}</p>
        <h1 className="mt-1 text-2xl font-bold">{title}</h1>
    </div>}>
        <Head title={title} />
        <PageContainer className="space-y-6 py-8">
            <div>
                <Link href="/admin" className="text-sm font-semibold text-indigo-700 hover:underline dark:text-indigo-300">
                    ← {text(dictionary, 'back_to_admin', 'Back to Admin')}
                </Link>
                <p className="mt-3 max-w-3xl text-secondary">
                    {text(dictionary, 'users_intro', 'Review accounts and manage approved administrative roles.')}
                </p>
            </div>

            <Surface className="p-5 md:p-6">
                <form role="search" onSubmit={submitFilters} className="grid gap-4 md:grid-cols-4 md:items-end">
                    <div>
                        <label htmlFor="user-search" className="block text-sm font-semibold">{text(dictionary, 'search', 'Search name or email')}</label>
                        <input
                            id="user-search"
                            type="search"
                            maxLength={100}
                            value={filterState.search}
                            onChange={(event) => setFilterState({ ...filterState, search: event.target.value })}
                            className="app-input mt-2 w-full"
                        />
                    </div>
                    <div>
                        <label htmlFor="user-status" className="block text-sm font-semibold">{text(dictionary, 'status', 'Verification status')}</label>
                        <select
                            id="user-status"
                            value={filterState.status}
                            onChange={(event) => setFilterState({ ...filterState, status: event.target.value })}
                            className="app-input mt-2 w-full"
                        >
                            <option value="">{text(dictionary, 'all', 'All')}</option>
                            <option value="verified">{text(dictionary, 'verified', 'Verified')}</option>
                            <option value="unverified">{text(dictionary, 'unverified', 'Unverified')}</option>
                        </select>
                    </div>
                    <div>
                        <label htmlFor="user-role" className="block text-sm font-semibold">{text(dictionary, 'role', 'Administrative role')}</label>
                        <select
                            id="user-role"
                            value={filterState.role}
                            onChange={(event) => setFilterState({ ...filterState, role: event.target.value })}
                            className="app-input mt-2 w-full"
                        >
                            <option value="">{text(dictionary, 'all', 'All')}</option>
                            {role_options.map((role) => <option key={role.value} value={role.value}>{role.label}</option>)}
                            <option value="none">{text(dictionary, 'no_admin_role', 'No administrative role')}</option>
                        </select>
                    </div>
                    <button type="submit" className="app-button app-button-secondary">
                        {text(dictionary, 'apply_filters', 'Apply filters')}
                    </button>
                </form>
            </Surface>

            {users.data.length === 0 ? <StatePanel
                title={text(dictionary, 'no_users_title', 'No users found')}
                description={text(dictionary, 'no_users_description', 'Try changing the approved filters.')}
            /> : <Surface className="overflow-hidden">
                <div className="overflow-x-auto">
                    <table aria-label={title} className="w-full min-w-[800px] border-collapse text-left">
                        <thead className="border-b border-[var(--app-border-quiet)] bg-[var(--app-surface-muted)] text-sm">
                            <tr>
                                <th scope="col" className="p-4">{text(dictionary, 'user', 'User')}</th>
                                <th scope="col" className="p-4">{text(dictionary, 'status', 'Verification status')}</th>
                                <th scope="col" className="p-4">{text(dictionary, 'administrative_roles', 'Administrative roles')}</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-[var(--app-border-quiet)]">
                            {users.data.map((user) => <tr key={user.user_id} className="align-top">
                                <td className="p-4">
                                    <span className="block font-semibold">{user.name}</span>
                                    <span className="mt-1 block text-sm text-secondary">{user.email}</span>
                                </td>
                                <td className="p-4">
                                    <span className="app-badge">
                                        {user.verified
                                            ? text(dictionary, 'verified', 'Verified')
                                            : text(dictionary, 'unverified', 'Unverified')}
                                    </span>
                                </td>
                                <td className="p-4">
                                    <UserRoleForm
                                        key={user.user_id}
                                        user={user}
                                        roleOptions={role_options}
                                        canManageSuperAdmin={capabilities.manage_super_admin}
                                        dictionary={dictionary}
                                        currentUserId={auth.user!.user_id}
                                    />
                                </td>
                            </tr>)}
                        </tbody>
                    </table>
                </div>
            </Surface>}

            {users.last_page > 1 && <nav aria-label={text(dictionary, 'pagination', 'User pagination')} className="flex flex-wrap gap-2">
                {users.links.map((link) => link.url
                    ? <Link
                        key={link.label}
                        href={link.url}
                        aria-current={link.active ? 'page' : undefined}
                        className={`app-button ${link.active ? 'app-button-primary' : 'app-button-secondary'}`}
                    >
                        {paginationLabel(link.label)}
                    </Link>
                    : <span key={link.label} aria-disabled="true" className="app-button app-button-secondary opacity-50">
                        {paginationLabel(link.label)}
                    </span>)}
            </nav>}
        </PageContainer>
    </AppLayout>;
}
