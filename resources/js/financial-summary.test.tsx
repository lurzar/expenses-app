// @vitest-environment jsdom

import { act, cleanup, fireEvent, render, screen } from '@testing-library/react';
import { afterEach, describe, expect, it, vi } from 'vitest';

const { deleteRequest } = vi.hoisted(() => ({ deleteRequest: vi.fn() }));

vi.mock('@inertiajs/react', () => ({
    Head: () => null,
    Link: ({ children, href, ...props }: React.AnchorHTMLAttributes<HTMLAnchorElement>) => <a href={String(href)} {...props}>{children}</a>,
    router: { delete: deleteRequest },
}));

vi.mock('./Layouts/AppLayout', () => ({
    default: ({ children, header }: React.PropsWithChildren<{ header?: React.ReactNode }>) => <div>{header}{children}</div>,
}));

import Dashboard from './Pages/Dashboard/Index';
import ExpensesShow from './Pages/Expenses/Show';
import PlanningShow from './Pages/Planning/Show';
import FinancialChartPanel from './Components/Financial/FinancialChartPanel';
import { Planning, User } from './types';

const user: User = {
    user_id: '01PUBLICUSER',
    name: 'Amina',
    email: 'amina@example.test',
    email_verified_at: '2026-08-16T00:00:00+08:00',
    created_at: '2026-08-16T00:00:00+08:00',
    updated_at: '2026-08-16T00:00:00+08:00',
};

const augustPlan: Planning = {
    planning_id: '01AUGUSTPLAN',
    month: 8,
    year: 2026,
    name: 'August 2026',
    salary: '5000.00',
    saving_rate: '20.00',
    spending: '2700.00',
    sections: {
        savings: [{ item: 'Emergency fund', amount: '800.00' }],
        commitments: [{ item: 'Rent', amount: '2000.00' }],
        others: [{ item: 'Living', amount: '700.00' }],
    },
    totals: {
        target_savings: '1000.00',
        savings: '800.00',
        commitments: '2000.00',
        others: '700.00',
        spending: '2700.00',
        allocated: '3500.00',
        balance: '1500.00',
    },
    created_at: '2026-08-16T00:00:00+08:00',
    updated_at: '2026-08-16T00:00:00+08:00',
};

const julyPlan: Planning = {
    ...augustPlan,
    planning_id: '01JULYPLAN',
    month: 7,
    name: 'July 2026',
    salary: '4800.00',
    totals: { ...augustPlan.totals, allocated: '3300.00', balance: '1500.00' },
};

const zeroPlan: Planning = {
    ...augustPlan,
    planning_id: '01ZEROPLAN',
    name: 'September 2026',
    month: 9,
    salary: '0.00',
    saving_rate: '0.00',
    spending: '0.00',
    sections: { savings: [], commitments: [], others: [] },
    totals: {
        target_savings: '0.00', savings: '0.00', commitments: '0.00', others: '0.00',
        spending: '0.00', allocated: '0.00', balance: '0.00',
    },
};

const props = { auth: { user }, flash: { message: null, success: null, error: null } };

afterEach(() => {
    cleanup();
    deleteRequest.mockReset();
});

describe('banking-style Planning summaries', () => {
    it('renders the Planning hierarchy, exact chart alternatives, and allocation items', () => {
        render(<PlanningShow {...props} planning={augustPlan} />);

        const labels = ['Remaining planned balance', 'Monthly income', 'Savings allocation', 'Commitments', 'Other allocations'];
        const figures = labels.map((label) => screen.getAllByText(label)[0]);
        figures.slice(0, -1).forEach((figure, index) => expect(figure.compareDocumentPosition(figures[index + 1]) & Node.DOCUMENT_POSITION_FOLLOWING).toBeTruthy());
        expect(screen.getByRole('table', { name: 'Allocation composition exact values' })).not.toBeNull();
        expect(screen.getByRole('table', { name: 'Income and allocation exact values' })).not.toBeNull();
        expect(screen.getByRole('table', { name: 'Savings target exact values' })).not.toBeNull();
        expect(screen.getByText('Emergency fund')).not.toBeNull();
        expect(screen.getAllByText('RM 800.00').length).toBeGreaterThan(0);
    });

    it('uses an accessible delete dialog with safe initial focus and focus return', () => {
        render(<PlanningShow {...props} planning={augustPlan} />);

        const trigger = screen.getByRole('button', { name: 'Delete plan' });
        trigger.focus();
        fireEvent.click(trigger);

        const dialog = screen.getByRole('dialog', { name: 'Delete August 2026 plan?' });
        const cancel = screen.getByRole('button', { name: 'Keep plan' });
        expect(document.activeElement).toBe(cancel);

        fireEvent.keyDown(dialog, { key: 'Escape' });
        expect(screen.queryByRole('dialog')).toBeNull();
        expect(document.activeElement).toBe(trigger);
        expect(deleteRequest).not.toHaveBeenCalled();
    });

    it('announces delete progress, prevents duplicate requests, and restores context after failure', () => {
        render(<PlanningShow {...props} planning={augustPlan} />);

        fireEvent.click(screen.getByRole('button', { name: 'Delete plan' }));
        fireEvent.click(screen.getByRole('button', { name: 'Delete plan permanently' }));

        expect(deleteRequest).toHaveBeenCalledTimes(1);
        expect(deleteRequest.mock.calls[0][0]).toBe('/planning/01AUGUSTPLAN');
        expect(screen.getByRole('status').textContent).toContain('Deleting August 2026 plan');
        expect(screen.getByRole('button', { name: 'Deleting plan…' }).hasAttribute('disabled')).toBe(true);

        const callbacks = deleteRequest.mock.calls[0][1] as { onError: () => void; onFinish: () => void };
        act(() => { callbacks.onError(); callbacks.onFinish(); });

        expect(screen.getByRole('alert').textContent).toContain('could not be deleted');
        expect(document.activeElement).toBe(screen.getByRole('button', { name: 'Delete plan permanently' }));
    });

    it('labels Expenses as a read-only projection and never offers deletion', () => {
        render(<ExpensesShow {...props} planning={augustPlan} />);

        expect(screen.getAllByText('Expense projection').length).toBeGreaterThan(0);
        expect(screen.getByText(/planned allocation breakdown/i)).not.toBeNull();
        expect(screen.queryByRole('button', { name: /delete/i })).toBeNull();
        expect(screen.getByRole('link', { name: 'Open full plan' }).getAttribute('href')).toBe('/planning/01AUGUSTPLAN');
    });

    it('shows an explicit selected period and an exact multi-period trend on Dashboard', () => {
        render(<Dashboard {...props} plannings={[augustPlan, julyPlan]} />);

        expect(screen.getByText('Selected plan: August 2026')).not.toBeNull();
        expect(screen.queryByText(/this month/i)).toBeNull();
        expect(screen.getByRole('table', { name: 'Planned trend exact values' })).not.toBeNull();
        expect(screen.getAllByText('July 2026').length).toBeGreaterThan(0);
    });

    it('shows a purposeful empty Dashboard and hides an ineligible one-period trend', () => {
        const { rerender } = render(<Dashboard {...props} plannings={[]} />);

        expect(screen.getByText('Create your first monthly plan')).not.toBeNull();
        expect(screen.getByRole('link', { name: 'Create a plan' }).getAttribute('href')).toBe('/planning/create');
        expect(screen.queryByRole('table')).toBeNull();

        rerender(<Dashboard {...props} plannings={[augustPlan]} />);
        expect(screen.queryByRole('table', { name: 'Planned trend exact values' })).toBeNull();
        expect(screen.getByText('Selected plan: August 2026')).not.toBeNull();
    });

    it('keeps zero denominators unavailable and exact instead of fabricating percentages', () => {
        render(<PlanningShow {...props} planning={zeroPlan} />);

        expect(screen.getByText('No allocations added')).not.toBeNull();
        expect(screen.getByText('No savings target set')).not.toBeNull();
        expect(screen.getAllByText('Percentage unavailable').length).toBeGreaterThan(0);
        expect(document.body.textContent).not.toMatch(/NaN|Infinity/);
    });

    it('states the exact amount when savings exceed the target', () => {
        const overTargetPlan = {
            ...augustPlan,
            totals: { ...augustPlan.totals, savings: '1200.00', allocated: '3900.00', balance: '1100.00' },
        };

        render(<PlanningShow {...props} planning={overTargetPlan} />);

        expect(screen.getByText('Above savings target by RM 200.00')).not.toBeNull();
    });

    it('keeps the exact-value fallback when a chart visual is unavailable', () => {
        render(<FinancialChartPanel title="Comparison" description="Exact fallback"><p>Exact values</p></FinancialChartPanel>);

        expect(screen.getByRole('status').textContent).toContain('Visual unavailable');
        expect(screen.getByText('Exact values')).not.toBeNull();
    });
});
