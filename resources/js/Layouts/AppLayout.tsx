import { KeyboardEvent, PropsWithChildren, ReactNode, useEffect, useRef, useState } from 'react';
import { Link, usePage } from '@inertiajs/react';
import ThemeToggle from '@/Components/ThemeToggle';
import { PageProps, User } from '@/types';
import { route } from '@/utils/route';

interface AppLayoutProps { user: User; header?: ReactNode; }
interface AppPageProps extends PageProps { locale?: string; }
interface Destination { label: string; href: string; match: string; icon: 'dashboard' | 'planning' | 'expenses' | 'profile'; }

function t(translations: Record<string, unknown> | undefined, key: string, fallback: string): string {
    const value = translations?.[key];
    return typeof value === 'string' ? value : fallback;
}

function NavigationIcon({ name }: { name: Destination['icon'] }) {
    const paths = {
        dashboard: <><rect x="3" y="3" width="7" height="7" rx="1" /><rect x="14" y="3" width="7" height="7" rx="1" /><rect x="3" y="14" width="7" height="7" rx="1" /><rect x="14" y="14" width="7" height="7" rx="1" /></>,
        planning: <><path d="M7 3v3M17 3v3M4 9h16" /><rect x="4" y="5" width="16" height="16" rx="2" /><path d="m8 15 2 2 5-5" /></>,
        expenses: <><path d="M4 19V9M10 19V5M16 19v-7M22 19H2" /></>,
        profile: <><circle cx="12" cy="8" r="4" /><path d="M4 21a8 8 0 0 1 16 0" /></>,
    };
    return <svg aria-hidden="true" viewBox="0 0 24 24" className="h-5 w-5" fill="none" stroke="currentColor" strokeWidth="1.8">{paths[name]}</svg>;
}

function DestinationLinks({ destinations, currentPath, onNavigate, mobile = false, initialFocus = false }: { destinations: Destination[]; currentPath: string; onNavigate?: () => void; mobile?: boolean; initialFocus?: boolean; }) {
    return destinations.map((destination, index) => {
        const active = currentPath === destination.match || currentPath.startsWith(`${destination.match}/`);
        return <Link key={destination.label} href={destination.href} onClick={onNavigate} data-drawer-initial={initialFocus && index === 0 ? true : undefined} aria-current={active ? 'page' : undefined} className={mobile ? `app-bottom-link ${active ? 'is-active' : ''}` : `app-nav-link ${active ? 'is-active' : ''}`}>
            <NavigationIcon name={destination.icon} /><span>{destination.label}</span>
        </Link>;
    });
}

function AccountControls({ user, locale, common, compact = false }: { user: User; locale?: string; common?: Record<string, unknown>; compact?: boolean; }) {
    return <div className={compact ? 'space-y-3' : 'space-y-4'}>
        <div className="flex items-center gap-2" aria-label="Language">
            <a href={route('language', { language: 'en' })} lang="en" aria-current={locale === 'en' ? 'true' : undefined} className={`app-language-link ${locale === 'en' ? 'is-active' : ''}`}>EN</a>
            <a href={route('language', { language: 'my' })} lang="ms" aria-current={locale === 'my' ? 'true' : undefined} className={`app-language-link ${locale === 'my' ? 'is-active' : ''}`}>MY</a>
            <ThemeToggle className="app-icon-button ml-auto" />
        </div>
        <Link href={route('profile.edit')} className="app-account-link">
            <span className="app-avatar" aria-hidden="true">{user.name.slice(0, 1).toUpperCase()}</span>
            <span className="min-w-0"><span className="block truncate font-semibold">{user.name}</span><span className="block text-xs text-secondary">Profile and security</span></span>
        </Link>
        <Link href={route('logout')} method="post" as="button" className="app-logout-button">{t(common, 'logout', 'Logout')}</Link>
    </div>;
}

export default function AppLayout({ user, header, children }: PropsWithChildren<AppLayoutProps>) {
    const page = usePage<AppPageProps>();
    const { locale, translations, flash } = page.props;
    const common = translations?.common as Record<string, unknown> | undefined;
    const currentPath = page.url.split('?')[0];
    const [drawerOpen, setDrawerOpen] = useState(false);
    const drawerTrigger = useRef<HTMLButtonElement>(null);
    const drawer = useRef<HTMLElement>(null);
    const destinations: Destination[] = [
        { label: t(common, 'dashboard', 'Dashboard'), href: route('dashboard'), match: '/dashboard', icon: 'dashboard' },
        { label: t(common, 'planning', 'Planning'), href: route('planning.index'), match: '/planning', icon: 'planning' },
        { label: t(common, 'expenses', 'Expenses'), href: route('expenses.index'), match: '/expenses', icon: 'expenses' },
        { label: 'Profile', href: route('profile.edit'), match: '/profile', icon: 'profile' },
    ];
    const notice = flash?.success ?? flash?.message;

    const closeDrawer = (focusMain = false) => {
        setDrawerOpen(false);
        if (focusMain) document.getElementById('main-content')?.focus();
        else drawerTrigger.current?.focus();
    };

    useEffect(() => {
        if (!drawerOpen) return;
        const previousOverflow = document.body.style.overflow;
        document.body.style.overflow = 'hidden';
        drawer.current?.querySelector<HTMLElement>('[data-drawer-initial]')?.focus();
        const handleEscape = (event: globalThis.KeyboardEvent) => { if (event.key === 'Escape') closeDrawer(); };
        const handleBreakpointChange = () => { if (window.innerWidth < 768 || window.innerWidth >= 1024) closeDrawer(true); };
        document.addEventListener('keydown', handleEscape);
        window.addEventListener('resize', handleBreakpointChange);
        return () => { document.removeEventListener('keydown', handleEscape); window.removeEventListener('resize', handleBreakpointChange); document.body.style.overflow = previousOverflow; };
    }, [drawerOpen]);

    const trapDrawerFocus = (event: KeyboardEvent<HTMLElement>) => {
        if (event.key !== 'Tab' || !drawer.current) return;
        const focusable = [...drawer.current.querySelectorAll<HTMLElement>('a[href], button:not([disabled]), summary, [tabindex]:not([tabindex="-1"])')];
        const first = focusable[0];
        const last = focusable[focusable.length - 1];
        if (event.shiftKey && document.activeElement === first) { event.preventDefault(); last?.focus(); }
        else if (!event.shiftKey && document.activeElement === last) { event.preventDefault(); first?.focus(); }
    };

    return <div className="app-shell min-h-screen">
        <a href="#main-content" className="app-skip-link">Skip to main content</a>
        {(notice || flash?.warning || flash?.error) && <div className="app-flash-stack">
            {notice && <div role="status" aria-live="polite" className="app-flash-message app-flash-success">{notice}</div>}
            {flash?.warning && <div role="status" aria-live="polite" className="app-flash-message app-flash-warning">{flash.warning}</div>}
            {flash?.error && <div role="alert" aria-live="assertive" className="app-flash-message app-flash-error">{flash.error}</div>}
        </div>}
        <aside data-testid="desktop-sidebar" className="app-sidebar fixed inset-y-0 left-0 z-30 hidden w-64 flex-col lg:flex" aria-label="Application navigation">
            <Link href={route('landing')} className="app-brand"><span className="app-brand-mark" aria-hidden="true">E</span><span>Expenses</span></Link>
            <nav className="mt-8 flex-1 space-y-2"><DestinationLinks destinations={destinations.slice(0, 3)} currentPath={currentPath} /></nav>
            <AccountControls user={user} locale={locale} common={common} />
        </aside>
        <header data-testid="tablet-header" className="app-topbar hidden h-16 items-center gap-4 px-5 md:flex lg:hidden">
            <button ref={drawerTrigger} type="button" className="app-icon-button" aria-label="Open navigation" aria-expanded={drawerOpen} onClick={() => setDrawerOpen(true)}><svg aria-hidden="true" viewBox="0 0 24 24" className="h-6 w-6" fill="none" stroke="currentColor" strokeWidth="2"><path d="M4 7h16M4 12h16M4 17h16" /></svg></button>
            <Link href={route('landing')} className="app-brand compact"><span className="app-brand-mark" aria-hidden="true">E</span><span>Expenses</span></Link>
            <details className="app-account-menu ml-auto"><summary>Account</summary><div className="app-account-popover inline-end"><AccountControls user={user} locale={locale} common={common} compact /></div></details>
        </header>
        <header className="app-topbar flex h-14 items-center justify-between px-4 md:hidden">
            <Link href={route('landing')} className="app-brand compact"><span className="app-brand-mark" aria-hidden="true">E</span><span>Expenses</span></Link>
            <details className="app-account-menu"><summary>Account</summary><div className="app-account-popover right-0"><AccountControls user={user} locale={locale} common={common} compact /></div></details>
        </header>
        {drawerOpen && <div className="fixed inset-0 z-40 hidden md:block lg:hidden">
            <button type="button" className="absolute inset-0 bg-black/50" aria-label="Close navigation overlay" onClick={() => closeDrawer()} />
            <aside ref={drawer} role="dialog" aria-modal="true" aria-label="Application navigation" onKeyDown={trapDrawerFocus} className="app-drawer relative z-10 flex h-full w-[280px] flex-col p-5">
                <div className="flex items-center justify-between"><span className="app-brand"><span className="app-brand-mark" aria-hidden="true">E</span><span>Expenses</span></span><button type="button" className="app-icon-button" aria-label="Close navigation" onClick={() => closeDrawer()}>×</button></div>
                <nav className="mt-8 flex-1 space-y-2"><DestinationLinks destinations={destinations.slice(0, 3)} currentPath={currentPath} onNavigate={() => closeDrawer()} initialFocus /></nav>
                <AccountControls user={user} locale={locale} common={common} />
            </aside>
        </div>}
        <div className="lg:pl-64">
            {header && <header className="app-page-header"><div className="app-page-container py-5">{header}</div></header>}
            <main id="main-content" tabIndex={-1} className="min-h-[calc(100vh-4rem)] pb-24 text-primary md:pb-8">{children}</main>
        </div>
        <nav data-testid="mobile-bottom-nav" className="app-bottom-nav fixed inset-x-0 bottom-0 z-30 grid grid-cols-4 md:hidden" aria-label="Mobile application navigation"><DestinationLinks destinations={destinations} currentPath={currentPath} mobile /></nav>
    </div>;
}
