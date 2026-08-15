import { describe, expect, it } from 'vitest';
import { route } from '@/utils/route';
import appLayout from './Layouts/AppLayout.tsx?raw';
import guestLayout from './Layouts/GuestLayout.tsx?raw';

describe('language route contract', () => {
    it.each([
        ['en', '/language/en'],
        ['my', '/language/my'],
    ])('generates the supported %s URL', (language, expectedUrl) => {
        expect(route('language', { language })).toBe(expectedUrl);
    });

    it('rejects a missing language parameter', () => {
        expect(() => route('language')).toThrow(/language.*required/i);
    });

    it.each([
        ['AppLayout', appLayout],
        ['GuestLayout', guestLayout],
    ])('%s uses the live language parameter name', (_layout, source) => {
        expect(source).toContain("route('language', { language: 'en' })");
        expect(source).toContain("route('language', { language: 'my' })");
        expect(source).not.toContain("route('language', { lang:");
    });
});
