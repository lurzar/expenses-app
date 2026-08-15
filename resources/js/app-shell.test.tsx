// @vitest-environment jsdom
// @vitest-environment-options { "url": "https://expenses.test/planning" }

import { cleanup, fireEvent, render, screen } from '@testing-library/react';
import { afterEach, describe, expect, it, vi } from 'vitest';

vi.mock('@inertiajs/react', () => ({
    Link: ({ children, href, method: _method, as: _as, ...props }: React.AnchorHTMLAttributes<HTMLAnchorElement> & { method?: string; as?: string }) => (
        <a href={String(href)} {...props}>{children}</a>
    ),
    usePage: () => ({
        url: '/planning',
        props: { locale: 'en', translations: {} },
    }),
}));

vi.mock('./utils/route', () => ({
    route: (name: string, params?: { language?: string }) => params?.language ? `/language/${params.language}` : `/${name}`,
}));

vi.mock('./Components/ThemeToggle', () => ({
    default: ({ className }: { className?: string }) => <button type="button" className={className}>Dark mode</button>,
}));

import AppLayout from './Layouts/AppLayout';
import FinancialNumber from './Components/UI/FinancialNumber';
import MetricCard from './Components/UI/MetricCard';
import StatePanel from './Components/UI/StatePanel';

const user = {
    user_id: '01PUBLIC',
    name: 'Amina',
    email: 'amina@example.test',
    email_verified_at: null,
    created_at: '2026-08-16T00:00:00+08:00',
    updated_at: '2026-08-16T00:00:00+08:00',
};

afterEach(() => cleanup());

describe('authenticated financial application shell', () => {
    it('renders exact financial values through the shared metric primitive', () => {
        render(<MetricCard label="Monthly income" value="1234.50" context="August 2026" />);
        expect(screen.getByText('Monthly income')).not.toBeNull();
        expect(screen.getByText('RM 1,234.50')).not.toBeNull();
        expect(screen.getByText('August 2026')).not.toBeNull();
    });

    it('formats legacy negative and unavailable financial values accessibly', () => {
        const { rerender } = render(<FinancialNumber value="-125.50" />);

        expect(screen.getByText('−RM 125.50')).not.toBeNull();

        rerender(<FinancialNumber value={null} unavailableLabel="Balance unavailable" />);
        expect(screen.getByLabelText('Balance unavailable').textContent).toBe('RM —');
    });

    it('only announces state panels when the caller requests live feedback', () => {
        const { rerender } = render(<StatePanel title="No plans yet" description="Create your first plan." />);

        expect(screen.queryByRole('status')).toBeNull();
        expect(screen.queryByRole('alert')).toBeNull();

        rerender(<StatePanel title="Save failed" description="Try again." tone="danger" announce="assertive" />);
        expect(screen.getByRole('alert').getAttribute('aria-live')).toBe('assertive');
    });

    it('exposes the approved desktop, tablet, and mobile navigation models', () => {
        render(<AppLayout user={user} header={<h1>Planning</h1>}><p>Plan content</p></AppLayout>);

        expect(screen.getByTestId('desktop-sidebar').className).toContain('lg:flex');
        expect(screen.getByTestId('tablet-header').className).toContain('md:flex');
        expect(screen.getByTestId('mobile-bottom-nav').className).toContain('md:hidden');
        expect(screen.getByTestId('mobile-bottom-nav').querySelectorAll('a')).toHaveLength(4);
        expect(screen.getAllByRole('link', { name: 'Planning' }).some((link) => link.getAttribute('aria-current') === 'page')).toBe(true);
        expect(screen.getByRole('link', { name: 'Skip to main content' }).getAttribute('href')).toBe('#main-content');
    });

    it('opens, traps, and restores focus for the tablet drawer', () => {
        render(<AppLayout user={user} header={<h1>Planning</h1>}><p>Plan content</p></AppLayout>);

        const trigger = screen.getByRole('button', { name: 'Open navigation' });
        trigger.focus();
        fireEvent.click(trigger);

        const drawer = screen.getByRole('dialog', { name: 'Application navigation' });
        const close = screen.getByRole('button', { name: 'Close navigation' });

        expect(drawer).not.toBeNull();
        expect(document.activeElement).toBe(screen.getAllByRole('link', { name: 'Dashboard' })[1]);

        close.focus();
        fireEvent.keyDown(drawer, { key: 'Tab' });
        expect(drawer.contains(document.activeElement)).toBe(true);

        fireEvent.keyDown(document, { key: 'Escape' });
        expect(screen.queryByRole('dialog', { name: 'Application navigation' })).toBeNull();
        expect(document.activeElement).toBe(trigger);
    });

    it('closes the tablet drawer and restores scrolling when leaving its breakpoint', () => {
        Object.defineProperty(window, 'innerWidth', { configurable: true, value: 800 });
        render(<AppLayout user={user} header={<h1>Planning</h1>}><p>Plan content</p></AppLayout>);

        fireEvent.click(screen.getByRole('button', { name: 'Open navigation' }));
        expect(document.body.style.overflow).toBe('hidden');

        Object.defineProperty(window, 'innerWidth', { configurable: true, value: 1200 });
        fireEvent(window, new Event('resize'));

        expect(screen.queryByRole('dialog', { name: 'Application navigation' })).toBeNull();
        expect(document.body.style.overflow).toBe('');
    });
});
