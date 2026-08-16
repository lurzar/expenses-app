// @vitest-environment jsdom
// @vitest-environment-options { "url": "https://expenses.test/admin" }

import { cleanup, render, screen } from '@testing-library/react';
import { afterEach, describe, expect, it, vi } from 'vitest';

const { pageState } = vi.hoisted(() => ({
    pageState: {
        url: '/admin',
        props: {
            auth: { capabilities: { access_admin: true } },
            locale: 'en',
            translations: {
                common: { admin: 'Admin' },
                admin: {
                    title: 'Admin control plane',
                    eyebrow: 'System administration',
                    description: 'Manage platform capabilities without accessing private planning data.',
                    navigation_label: 'Admin navigation',
                    available_tools: 'Available tools',
                    no_tools_title: 'No management tools enabled',
                    no_tools_description: 'Additional tools will appear when their modules are delivered and you have access.',
                    access_status: 'Access protected',
                    access_status_description: 'Laravel verifies your capability on every Admin request.',
                },
            },
            flash: { message: null as string | null, success: null as string | null, warning: null as string | null, error: null as string | null },
        },
    },
}));

vi.mock('@inertiajs/react', () => ({
    Head: ({ title }: { title: string }) => <span data-testid="head-title">{title}</span>,
    Link: ({ children, href, method: _method, as: _as, ...props }: React.AnchorHTMLAttributes<HTMLAnchorElement> & { method?: string; as?: string }) => (
        <a href={String(href)} {...props}>{children}</a>
    ),
    usePage: () => pageState,
}));

vi.mock('./utils/route', () => ({
    route: (name: string, params?: { language?: string }) => {
        if (params?.language) return `/language/${params.language}`;
        const routes: Record<string, string> = {
            landing: '/',
            dashboard: '/dashboard',
            'planning.index': '/planning',
            'expenses.index': '/expenses',
            'profile.edit': '/profile',
            logout: '/logout',
            'admin.index': '/admin',
        };
        return routes[name] ?? `/${name}`;
    },
}));

vi.mock('./Components/ThemeToggle', () => ({
    default: ({ className }: { className?: string }) => <button type="button" className={className}>Dark mode</button>,
}));

import AdminIndex, { AdminIndexProps } from './Pages/Admin/Index';

const user = {
    user_id: '01ADMINPUBLIC',
    name: 'Amina',
    email: 'amina@example.test',
    email_verified_at: '2026-08-16T00:00:00+08:00',
    created_at: '2026-08-16T00:00:00+08:00',
    updated_at: '2026-08-16T00:00:00+08:00',
};

const props: AdminIndexProps = {
    auth: { user, capabilities: { access_admin: true } },
    admin: {
        navigation: [{
            key: 'overview',
            label: 'Overview',
            description: 'Admin control plane status',
            href: '/admin',
        }],
    },
    flash: { message: null, success: null, warning: null, error: null },
};

afterEach(() => {
    cleanup();
    pageState.props.locale = 'en';
    pageState.props.translations.admin = {
        title: 'Admin control plane',
        eyebrow: 'System administration',
        description: 'Manage platform capabilities without accessing private planning data.',
        navigation_label: 'Admin navigation',
        available_tools: 'Available tools',
        no_tools_title: 'No management tools enabled',
        no_tools_description: 'Additional tools will appear when their modules are delivered and you have access.',
        access_status: 'Access protected',
        access_status_description: 'Laravel verifies your capability on every Admin request.',
    };
    pageState.props.flash = { message: null, success: null, warning: null, error: null };
});

describe('Admin control plane shell', () => {
    it('renders a focused overview and only server-delivered navigation', () => {
        render(<AdminIndex {...props} />);

        expect(screen.getByTestId('head-title').textContent).toBe('Admin control plane');
        expect(screen.getByRole('heading', { level: 1, name: 'Admin control plane' })).not.toBeNull();
        expect(screen.getByRole('navigation', { name: 'Admin navigation' })).not.toBeNull();
        expect(screen.getByRole('link', { name: /Overview/ }).getAttribute('href')).toBe('/admin');
        expect(screen.getByText('Access protected')).not.toBeNull();
        expect(screen.getByText('No management tools enabled')).not.toBeNull();
        expect(screen.queryByText(/billing/i)).toBeNull();
        expect(screen.queryByText(/tenant/i)).toBeNull();
    });

    it('uses the active Malay dictionary for the Admin shell', () => {
        pageState.props.locale = 'my';
        pageState.props.translations.admin = {
            title: 'Panel kawalan pentadbir',
            eyebrow: 'Pentadbiran sistem',
            description: 'Urus keupayaan platform tanpa mengakses data perancangan peribadi.',
            navigation_label: 'Navigasi pentadbir',
            available_tools: 'Alat tersedia',
            no_tools_title: 'Tiada alat pengurusan diaktifkan',
            no_tools_description: 'Alat tambahan akan muncul apabila modulnya dihantar dan anda mempunyai akses.',
            access_status: 'Akses dilindungi',
            access_status_description: 'Laravel mengesahkan keupayaan anda pada setiap permintaan Pentadbir.',
        };

        render(<AdminIndex {...props} />);

        expect(screen.getByRole('heading', { level: 1, name: 'Panel kawalan pentadbir' })).not.toBeNull();
        expect(screen.getByRole('navigation', { name: 'Navigasi pentadbir' })).not.toBeNull();
        expect(screen.getByText('Tiada alat pengurusan diaktifkan')).not.toBeNull();
    });

    it('announces server errors and keeps the empty state non-live', () => {
        pageState.props.flash.error = 'Admin tools could not be loaded.';
        render(<AdminIndex {...props} />);

        expect(screen.getByRole('alert').textContent).toContain('could not be loaded');
        expect(screen.getByText('No management tools enabled').closest('[role]')).toBeNull();
    });
});
