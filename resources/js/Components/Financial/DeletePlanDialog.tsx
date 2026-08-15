import { KeyboardEvent, useEffect, useRef, useState } from 'react';
import { router } from '@inertiajs/react';

export default function DeletePlanDialog({ planningId, planningName }: { planningId: string; planningName: string }) {
    const [open, setOpen] = useState(false);
    const [pending, setPending] = useState(false);
    const [error, setError] = useState<string | null>(null);
    const trigger = useRef<HTMLButtonElement>(null);
    const dialog = useRef<HTMLDivElement>(null);
    const cancel = useRef<HTMLButtonElement>(null);
    const confirm = useRef<HTMLButtonElement>(null);

    const close = () => {
        if (pending) return;
        setOpen(false);
        setError(null);
        trigger.current?.focus();
    };

    useEffect(() => {
        if (!open) return;
        cancel.current?.focus();
        const onEscape = (event: globalThis.KeyboardEvent) => { if (event.key === 'Escape') close(); };
        document.addEventListener('keydown', onEscape);
        return () => document.removeEventListener('keydown', onEscape);
    }, [open, pending]);

    useEffect(() => {
        if (error && !pending) confirm.current?.focus();
    }, [error, pending]);

    const trapFocus = (event: KeyboardEvent<HTMLDivElement>) => {
        if (event.key !== 'Tab' || !dialog.current) return;
        const controls = [...dialog.current.querySelectorAll<HTMLElement>('button:not([disabled])')];
        const first = controls[0];
        const last = controls[controls.length - 1];
        if (event.shiftKey && document.activeElement === first) { event.preventDefault(); last?.focus(); }
        else if (!event.shiftKey && document.activeElement === last) { event.preventDefault(); first?.focus(); }
    };

    const confirmDelete = () => {
        setPending(true);
        setError(null);
        router.delete(`/planning/${planningId}`, {
            onError: () => setError('The plan could not be deleted. Try again.'),
            onFinish: () => setPending(false),
        });
    };

    return <>
        <button ref={trigger} type="button" className="button-danger" onClick={() => setOpen(true)}>Delete plan</button>
        {open && <div className="dialog-layer"><button type="button" className="dialog-backdrop" aria-label="Close delete dialog" onClick={close} disabled={pending} />
            <div ref={dialog} role="dialog" aria-modal="true" aria-labelledby="delete-plan-title" aria-describedby="delete-plan-description" aria-busy={pending} onKeyDown={trapFocus} className="delete-dialog">
                <h2 id="delete-plan-title" className="text-xl font-bold">Delete {planningName} plan?</h2>
                <p id="delete-plan-description" className="mt-3 text-sm text-secondary">This removes the monthly plan from Planning and all of its projections. This action cannot be undone.</p>
                {pending && <p role="status" className="mt-3 text-sm text-secondary">Deleting {planningName} plan…</p>}
                {error && <p role="alert" className="mt-3 text-sm state-danger">{error}</p>}
                <div className="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end"><button ref={cancel} type="button" className="button-secondary" onClick={close} disabled={pending}>Keep plan</button><button ref={confirm} type="button" className="button-danger" onClick={confirmDelete} disabled={pending}>{pending ? 'Deleting plan…' : 'Delete plan permanently'}</button></div>
            </div>
        </div>}
    </>;
}
