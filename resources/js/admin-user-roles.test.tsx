// @vitest-environment jsdom
// @vitest-environment-options { "url": "https://expenses.test/admin/users" }

import React from 'react';
import { act, cleanup, fireEvent, render, screen } from '@testing-library/react';
import { afterEach, describe, expect, it, vi } from 'vitest';

const { formErrorSetter, getSpy, patchSpy, submittedDataSpy, pageState } = vi.hoisted(() => ({
    formErrorSetter: {
        current: null as null | ((errors: Record<string, string>) => void),
    },
    getSpy: vi.fn(),
    patchSpy: vi.fn(),
    submittedDataSpy: vi.fn(),
    pageState: {
        url: '/admin/users',
        props: {
            auth: { capabilities: { access_admin: true } },
            locale: 'en',
            translations: {
                common: { admin: 'Admin' },
                admin: {
                    users_title: 'Users and roles',
                    users_eyebrow: 'User administration',
                    users_intro: 'Review accounts and manage approved administrative roles.',
                    filters: 'Filter users',
                    search: 'Search name or email',
                    status: 'Verification status',
                    role: 'Administrative role',
                    all: 'All',
                    verified: 'Verified',
                    unverified: 'Unverified',
                    no_admin_role: 'No administrative role',
                    apply_filters: 'Apply filters',
                    user: 'User',
                    administrative_roles: 'Administrative roles',
                    actions: 'Actions',
                    save_roles: 'Save roles',
                    protected_role: 'Protected role',
                    confirm_roles: 'Apply these administrative role changes?',
                    no_users_title: 'No users found',
                    no_users_description: 'Try changing the approved filters.',
                },
            },
            flash: { message: null, success: null, warning: null, error: null },
        },
    },
}));

vi.mock('@inertiajs/react', () => ({
    Head: ({ title }: { title: string }) => <span data-testid="head-title">{title}</span>,
    Link: ({ children, href, ...props }: React.AnchorHTMLAttributes<HTMLAnchorElement>) => <a href={String(href)} {...props}>{children}</a>,
    router: { get: getSpy },
    usePage: () => pageState,
    useForm: <T extends Record<string, unknown>>(initial: T) => {
        const [data, setDataState] = React.useState(initial);
        const [errors, setErrors] = React.useState<Record<string, string>>({});
        formErrorSetter.current = setErrors;

        return {
            data,
            setData: (key: keyof T | T, value?: unknown) => {
                if (typeof key === 'object') {
                    setDataState(key);
                    return;
                }

                setDataState((current) => ({ ...current, [key]: value }));
            },
            patch: (url: string, options: Record<string, unknown>) => {
                submittedDataSpy(data);
                patchSpy(url, options);
            },
            processing: false,
            errors,
        };
    },
}));

vi.mock('./utils/route', () => ({
    route: (name: string) => name === 'admin.index' ? '/admin' : '/',
}));

vi.mock('./Components/ThemeToggle', () => ({
    default: () => <button type="button">Dark mode</button>,
}));

import AdminUsersIndex, { AdminUsersIndexProps } from './Pages/Admin/Users/Index';

const user = {
    user_id: '01ADMINPUBLIC',
    name: 'Amina',
    email: 'amina@example.test',
    email_verified_at: '2026-08-16T00:00:00+08:00',
    created_at: '2026-08-16T00:00:00+08:00',
    updated_at: '2026-08-16T00:00:00+08:00',
};

const props: AdminUsersIndexProps = {
    auth: { user, capabilities: { access_admin: true } },
    flash: { message: null, success: null, warning: null, error: null },
    filters: { search: 'Amina', status: 'verified', role: '' },
    capabilities: { manage_super_admin: false },
    role_options: [
        { value: 'admin', label: 'Admin', description: 'Standard administration.', permissions: ['View users', 'Manage roles'] },
        { value: 'super-admin', label: 'Super-admin', description: 'Protected administration.', permissions: ['View users', 'Manage roles', 'Manage super-admins'] },
    ],
    users: {
        data: [{
            user_id: '01SUBJECTPUBLIC',
            name: 'Sara',
            email: 'sara@example.test',
            verified: true,
            roles: [],
            authorization_version: 2,
        }],
        links: [],
        current_page: 1,
        last_page: 1,
        from: 1,
        to: 1,
        total: 1,
    },
};

afterEach(() => {
    cleanup();
    getSpy.mockReset();
    patchSpy.mockReset();
    submittedDataSpy.mockReset();
    formErrorSetter.current = null;
    vi.restoreAllMocks();
});

describe('Admin user role management', () => {
    it('renders minimized account state with accessible filters and role controls', () => {
        render(<AdminUsersIndex {...props} />);

        expect(screen.getByRole('heading', { level: 1, name: 'Users and roles' })).not.toBeNull();
        expect(screen.getByRole('searchbox', { name: 'Search name or email' })).toHaveProperty('value', 'Amina');
        expect(screen.getByRole('table', { name: 'Users and roles' })).not.toBeNull();
        expect(screen.getByText('sara@example.test')).not.toBeNull();
        expect((screen.getByRole('checkbox', { name: 'Admin for Sara' }) as HTMLInputElement).disabled).toBe(false);
        expect((screen.getByRole('checkbox', { name: 'Super-admin for Sara' }) as HTMLInputElement).disabled).toBe(true);
        expect(screen.queryByText('01SUBJECTPUBLIC')).toBeNull();
        expect(screen.getAllByText('View users')).toHaveLength(2);
        expect(screen.getByText('Manage super-admins')).not.toBeNull();
    });

    it('submits approved filters and confirms role mutations', () => {
        vi.spyOn(window, 'confirm').mockReturnValue(true);
        render(<AdminUsersIndex {...props} />);

        fireEvent.submit(screen.getByRole('search'));
        expect(getSpy).toHaveBeenCalledWith('/admin/users', props.filters, expect.objectContaining({ preserveState: true }));

        fireEvent.click(screen.getByRole('checkbox', { name: 'Admin for Sara' }));
        fireEvent.click(screen.getByRole('button', { name: 'Save roles for Sara' }));

        expect(window.confirm).toHaveBeenCalledWith('Apply these administrative role changes?');
        expect(patchSpy).toHaveBeenCalledWith('/admin/users/01SUBJECTPUBLIC/roles', expect.objectContaining({ preserveScroll: true }));
    });

    it('uses the refreshed authorization revision for a second successful edit', () => {
        vi.spyOn(window, 'confirm').mockReturnValue(true);
        const { rerender } = render(<AdminUsersIndex {...props} />);

        fireEvent.click(screen.getByRole('checkbox', { name: 'Admin for Sara' }));
        fireEvent.click(screen.getByRole('button', { name: 'Save roles for Sara' }));
        expect(submittedDataSpy).toHaveBeenLastCalledWith({ roles: ['admin'], authorization_version: 2 });

        const refreshedUser = {
            ...props.users.data[0],
            roles: ['admin'] as ('admin' | 'super-admin')[],
            authorization_version: 3,
        };
        rerender(<AdminUsersIndex {...props} users={{ ...props.users, data: [refreshedUser] }} />);

        fireEvent.click(screen.getByRole('checkbox', { name: 'Admin for Sara' }));
        fireEvent.click(screen.getByRole('button', { name: 'Save roles for Sara' }));
        expect(submittedDataSpy).toHaveBeenLastCalledWith({ roles: [], authorization_version: 3 });
    });

    it('keeps a stale conflict visible while synchronizing refreshed account state', () => {
        const { rerender } = render(<AdminUsersIndex {...props} />);

        act(() => formErrorSetter.current?.({ authorization_version: 'The account roles changed. Refresh and try again.' }));

        const refreshedUser = {
            ...props.users.data[0],
            roles: ['admin'] as ('admin' | 'super-admin')[],
            authorization_version: 3,
        };
        rerender(<AdminUsersIndex {...props} users={{ ...props.users, data: [refreshedUser] }} />);

        expect(screen.getByRole('alert').textContent).toContain('The account roles changed. Refresh and try again.');
        expect((screen.getByRole('checkbox', { name: 'Admin for Sara' }) as HTMLInputElement).checked).toBe(true);
    });

    it('marks current and unavailable pagination links for assistive technology', () => {
        render(<AdminUsersIndex {...props} users={{
            ...props.users,
            last_page: 2,
            links: [
                { url: null, label: '&laquo; Previous', active: false },
                { url: '/admin/users?page=1', label: '1', active: true },
                { url: '/admin/users?page=2', label: '2', active: false },
            ],
        }} />);

        expect(screen.getByRole('link', { name: '1' }).getAttribute('aria-current')).toBe('page');
        expect(screen.getByText('‹ Previous').getAttribute('aria-disabled')).toBe('true');
    });

    it('renders a clear empty state without exposing a role form', () => {
        render(<AdminUsersIndex {...props} users={{ ...props.users, data: [], from: null, to: null, total: 0 }} />);

        expect(screen.getByText('No users found')).not.toBeNull();
        expect(screen.queryByRole('button', { name: /Save roles/ })).toBeNull();
    });
});
