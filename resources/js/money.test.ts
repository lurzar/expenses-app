import { describe, expect, it } from 'vitest';

import { calculatePlanningPreview, formatMYR, parseMoney } from './utils/money';

describe('money helpers', () => {
    it('parses and formats exact MYR decimals without binary float arithmetic', () => {
        expect(parseMoney('0.05')).toBe(5n);
        expect(parseMoney('12.34')).toBe(1234n);
        expect(parseMoney('1e3')).toBeNull();
        expect(formatMYR('1234567.80')).toBe('RM 1,234,567.80');
    });

    it('matches the server-authoritative worked example and half-up boundary', () => {
        expect(calculatePlanningPreview({
            income: '5000',
            savingRate: '20.00',
            savings: ['800'],
            commitments: ['2000.00'],
            others: ['700.0'],
        })).toEqual({
            targetSavings: '1000.00',
            savings: '800.00',
            commitments: '2000.00',
            others: '700.00',
            spending: '2700.00',
            allocated: '3500.00',
            balance: '1500.00',
        });

        expect(calculatePlanningPreview({
            income: '0.05',
            savingRate: '10',
            savings: [],
            commitments: [],
            others: [],
        }).targetSavings).toBe('0.01');
    });
});
