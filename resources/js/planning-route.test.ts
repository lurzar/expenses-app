import { describe, expect, it } from 'vitest';
import { route } from '@/utils/route';

describe('planning public route contract', () => {
    const planningId = '01K31N7F6C4J5YQ1D3T0A8B2XE';

    it('generates Planning and Expenses detail URLs from the public planning id', () => {
        expect(route('planning.show', { planning: planningId })).toBe(`/planning/${planningId}`);
        expect(route('expenses.show', { expense: planningId })).toBe(`/expenses/${planningId}`);
    });

    it('rejects missing or stale Expenses parameters', () => {
        expect(() => route('expenses.show')).toThrow(/expense.*required/i);
        expect(() => route('expenses.show', { expenses: planningId })).toThrow(/expense.*required/i);
    });
});
