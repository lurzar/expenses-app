// @vitest-environment jsdom
// @vitest-environment-options { "url": "https://expenses.test/admin/roles" }

import { cleanup, fireEvent, render, screen, within } from '@testing-library/react';
import { afterEach, describe, expect, it, vi } from 'vitest';

const { createRole, updateRole, retireRole } = vi.hoisted(() => ({
    createRole: vi.fn(),
    updateRole: vi.fn(),
    retireRole: vi.fn(),
}));

vi.mock('@inertiajs/react', () => ({
    Head: () => null,
    Link: ({ children, href, ...props }: React.AnchorHTMLAttributes<HTMLAnchorElement>) => <a href={String(href)} {...props}>{children}</a>,
    router: { delete: retireRole },
    useForm: (data: Record<string, unknown>) => ({
        data,
        setData: vi.fn(),
        post: createRole,
        patch: updateRole,
        processing: false,
        errors: {},
    }),
    usePage: () => ({
        props: {
            translations: {
                admin: {
                    roles_title: 'Roles and permissions',
                    roles_eyebrow: 'Authorization management',
                    back_to_admin: 'Back to Admin',
                    roles_intro: 'Protected roles are defined by code. Custom roles can use only approved capabilities.',
                    create_role: 'Create role',
                    save_role: 'Save role',
                    role_name: 'Role name',
                    retire_role: 'Retire role',
                    protected_role: 'Protected role',
                    assignments: 'assignments',
                    permission_catalog: 'Approved capability catalog',
                    confirm_retire_role: 'Retire this unassigned custom role?',
                },
            },
        },
    }),
}));

vi.mock('./Layouts/AppLayout', () => ({
    default: ({ children, header }: React.PropsWithChildren<{ header?: React.ReactNode }>) => <main>{header}{children}</main>,
}));

vi.mock('./Components/UI/PageContainer', () => ({ default: ({ children }: React.PropsWithChildren) => <div>{children}</div> }));
vi.mock('./Components/UI/Surface', () => ({ default: ({ children }: React.PropsWithChildren) => <section>{children}</section> }));

import RolesIndex from './Pages/Admin/Roles/Index';

const user = {
    user_id: '01SUPERADMINPUBLIC',
    name: 'Amina',
    email: 'amina@example.test',
    email_verified_at: '2026-08-16T00:00:00+08:00',
    created_at: '2026-08-16T00:00:00+08:00',
    updated_at: '2026-08-16T00:00:00+08:00',
};

const props = {
    auth: { user, capabilities: { access_admin: true } },
    flash: { message: null, success: null, warning: null, error: null },
    permission_groups: [{
        module: 'planning',
        permissions: [{ name: 'planning.view', label: 'View planning', description: 'View planning data.' }],
    }],
    roles: [
        { name: 'super-admin', protected: true, permissions: ['admin.access', 'roles.manage'], unknown_permissions: [], assignment_count: 1, updated_at: '2026-08-16T00:00:00+08:00' },
        { name: 'planning-reviewer', protected: false, permissions: ['planning.view'], unknown_permissions: [], assignment_count: 0, updated_at: '2026-08-16T00:00:00+08:00' },
    ],
};

afterEach(() => {
    cleanup();
    createRole.mockReset();
    updateRole.mockReset();
    retireRole.mockReset();
    vi.restoreAllMocks();
});

describe('Admin role management workspace', () => {
    it('keeps protected roles read-only and provides creation, update, and retirement controls for custom roles', () => {
        render(<RolesIndex {...props} />);

        expect(screen.getByRole('heading', { level: 1, name: 'Roles and permissions' })).not.toBeNull();
        const protectedRole = screen.getByText('super-admin').closest('section')!;
        expect(within(protectedRole).getByText('Protected role')).not.toBeNull();
        expect(within(protectedRole).queryByRole('button')).toBeNull();

        expect(screen.getAllByRole('button', { name: 'Create role' })).toHaveLength(1);
        expect(screen.getAllByRole('button', { name: 'Save role' })).toHaveLength(1);
        expect(screen.getByRole('button', { name: 'Retire role' })).not.toBeNull();
        expect(screen.getByText('Approved capability catalog')).not.toBeNull();
    });

    it('sends create, update, and confirmed retirement requests only to the protected role endpoints', () => {
        vi.spyOn(window, 'confirm').mockReturnValue(true);
        render(<RolesIndex {...props} />);

        fireEvent.submit(screen.getByRole('button', { name: 'Create role' }).closest('form')!);
        fireEvent.submit(screen.getByRole('button', { name: 'Save role' }).closest('form')!);
        fireEvent.click(screen.getByRole('button', { name: 'Retire role' }));

        expect(createRole).toHaveBeenCalledWith('/admin/roles', { preserveScroll: true });
        expect(updateRole).toHaveBeenCalledWith('/admin/roles/planning-reviewer', { preserveScroll: true });
        expect(retireRole).toHaveBeenCalledWith('/admin/roles/planning-reviewer', { preserveScroll: true });
    });
});
