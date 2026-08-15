import { describe, expect, it } from 'vitest';

import { nextTheme, resolveTheme, themeToggleLabel } from './theme';

describe('resolveTheme', () => {
    it('uses a saved light preference before a dark system preference', () => {
        expect(resolveTheme('light', true)).toBe('light');
    });

    it('uses a saved dark preference before a light system preference', () => {
        expect(resolveTheme('dark', false)).toBe('dark');
    });

    it('ignores an invalid saved value and follows a dark system preference', () => {
        expect(resolveTheme('system', true)).toBe('dark');
    });

    it('defaults to light without a saved or dark system preference', () => {
        expect(resolveTheme(null, false)).toBe('light');
    });
});

describe('nextTheme', () => {
    it('toggles between the two supported themes', () => {
        expect(nextTheme('light')).toBe('dark');
        expect(nextTheme('dark')).toBe('light');
    });
});

describe('themeToggleLabel', () => {
    it('describes the action the control will perform', () => {
        expect(themeToggleLabel('light')).toBe('Switch to dark mode');
        expect(themeToggleLabel('dark')).toBe('Switch to light mode');
    });
});
