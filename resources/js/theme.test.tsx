// @vitest-environment jsdom
// @vitest-environment-options { "url": "https://expenses.test/" }

import { StrictMode } from 'react';
import { act, cleanup, render, renderHook, screen } from '@testing-library/react';
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';

import ThemeToggle from './Components/ThemeToggle';
import appLayoutSource from './Layouts/AppLayout.tsx?raw';
import guestLayoutSource from './Layouts/GuestLayout.tsx?raw';
import bladeSource from '../views/app.blade.php?raw';
import {
    nextTheme,
    resolveTheme,
    themeToggleLabel,
    useTheme,
} from './theme';

function useSystemTheme(prefersDark: boolean): void {
    Object.defineProperty(window, 'matchMedia', {
        configurable: true,
        value: vi.fn().mockReturnValue({ matches: prefersDark }),
    });
}

function installStorage(): Storage {
    const values = new Map<string, string>();
    const storage = {
        get length() {
            return values.size;
        },
        clear: vi.fn(() => values.clear()),
        getItem: vi.fn((key: string) => values.get(key) ?? null),
        key: vi.fn((index: number) => [...values.keys()][index] ?? null),
        removeItem: vi.fn((key: string) => values.delete(key)),
        setItem: vi.fn((key: string, value: string) => values.set(key, value)),
    } satisfies Storage;

    Object.defineProperty(window, 'localStorage', {
        configurable: true,
        value: storage,
    });

    return storage;
}

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
    it('provides a stable name for the pressed state', () => {
        expect(themeToggleLabel()).toBe('Dark mode');
    });
});

describe('useTheme', () => {
    let storage: Storage;

    beforeEach(() => {
        useSystemTheme(false);
        storage = installStorage();
        document.documentElement.classList.remove('dark');
        document.documentElement.style.colorScheme = '';
    });

    afterEach(() => {
        cleanup();
        vi.restoreAllMocks();
    });

    it('applies and stores one theme change when toggled in Strict Mode', () => {
        window.localStorage.setItem('theme', 'light');
        const classToggle = vi.spyOn(document.documentElement.classList, 'toggle');
        const storageWrite = vi.spyOn(storage, 'setItem');
        const { result } = renderHook(() => useTheme(), {
            wrapper: StrictMode,
        });

        classToggle.mockClear();
        storageWrite.mockClear();

        act(() => result.current.toggleTheme());

        expect(result.current.theme).toBe('dark');
        expect(classToggle).toHaveBeenCalledOnce();
        expect(classToggle).toHaveBeenCalledWith('dark', true);
        expect(storageWrite).toHaveBeenCalledOnce();
        expect(storageWrite).toHaveBeenCalledWith('theme', 'dark');
        expect(document.documentElement.style.colorScheme).toBe('dark');
    });

    it('falls back to the system theme when storage cannot be read', () => {
        useSystemTheme(true);
        vi.spyOn(storage, 'getItem').mockImplementation(() => {
            throw new Error('storage unavailable');
        });

        const { result } = renderHook(() => useTheme());

        expect(result.current.theme).toBe('dark');
        expect(document.documentElement.classList.contains('dark')).toBe(true);
    });

    it('still updates the rendered theme when storage cannot be written', () => {
        vi.spyOn(storage, 'setItem').mockImplementation(() => {
            throw new Error('storage unavailable');
        });
        const { result } = renderHook(() => useTheme());

        act(() => result.current.toggleTheme());

        expect(result.current.theme).toBe('dark');
        expect(document.documentElement.classList.contains('dark')).toBe(true);
        expect(document.documentElement.style.colorScheme).toBe('dark');
    });
});

describe('ThemeToggle', () => {
    beforeEach(() => {
        useSystemTheme(false);
        installStorage();
    });

    afterEach(() => {
        cleanup();
        vi.restoreAllMocks();
    });

    it('uses native button semantics and exposes the rendered theme state', () => {
        render(<ThemeToggle className="theme-control" />);

        const button = screen.getByRole('button', { name: 'Dark mode' });

        expect(button.tagName).toBe('BUTTON');
        expect(button.getAttribute('type')).toBe('button');
        expect(button.getAttribute('aria-pressed')).toBe('false');

        act(() => button.click());

        expect(button.getAttribute('aria-pressed')).toBe('true');
        expect(document.documentElement.classList.contains('dark')).toBe(true);
    });
});

describe('theme integration', () => {
    it('uses the shared control in guest and authenticated layouts', () => {
        expect(appLayoutSource).toContain("import ThemeToggle from '@/Components/ThemeToggle'");
        expect(appLayoutSource).toContain('<ThemeToggle');
        expect(guestLayoutSource).toContain("import ThemeToggle from '@/Components/ThemeToggle'");
        expect(guestLayoutSource).toContain('<ThemeToggle');
    });

    it('applies the saved or system theme before Vite loads', () => {
        const bootstrapStart = bladeSource.indexOf('<script>');
        const viteStart = bladeSource.indexOf('@viteReactRefresh');

        expect(bootstrapStart).toBeGreaterThan(-1);
        expect(viteStart).toBeGreaterThan(bootstrapStart);
        expect(bladeSource).toContain("window.localStorage.getItem('theme')");
        expect(bladeSource).toContain("window.matchMedia('(prefers-color-scheme: dark)')");
        expect(bladeSource).toContain("classList.toggle('dark', theme === 'dark')");
        expect(bladeSource).toContain('style.colorScheme = theme');
    });
});
