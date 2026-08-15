import { useCallback, useEffect, useState } from 'react';

export type Theme = 'light' | 'dark';

const storageKey = 'theme';

export function resolveTheme(
    storedTheme: string | null,
    prefersDark: boolean,
): Theme {
    if (storedTheme === 'light' || storedTheme === 'dark') {
        return storedTheme;
    }

    return prefersDark ? 'dark' : 'light';
}

export function nextTheme(theme: Theme): Theme {
    return theme === 'light' ? 'dark' : 'light';
}

export function themeToggleLabel(): string {
    return 'Dark mode';
}

export function initialTheme(): Theme {
    if (typeof window === 'undefined') {
        return 'light';
    }

    const prefersDark = window.matchMedia(
        '(prefers-color-scheme: dark)',
    ).matches;

    try {
        return resolveTheme(window.localStorage.getItem(storageKey), prefersDark);
    } catch {
        return resolveTheme(null, prefersDark);
    }
}

export function applyTheme(theme: Theme): void {
    if (typeof document === 'undefined') {
        return;
    }

    document.documentElement.classList.toggle('dark', theme === 'dark');
    document.documentElement.style.colorScheme = theme;
}

function persistTheme(theme: Theme): void {
    try {
        window.localStorage.setItem(storageKey, theme);
    } catch {
        // Theme selection remains functional when storage is unavailable.
    }

}

export function useTheme(): {
    theme: Theme;
    toggleTheme: () => void;
} {
    const [theme, setTheme] = useState<Theme>(initialTheme);

    useEffect(() => {
        applyTheme(theme);
    }, [theme]);

    const toggleTheme = useCallback(() => {
        const updatedTheme = nextTheme(theme);

        persistTheme(updatedTheme);
        setTheme(updatedTheme);
    }, [theme]);

    return { theme, toggleTheme };
}
