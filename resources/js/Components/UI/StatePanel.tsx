import { ReactNode } from 'react';
import Surface from './Surface';

export default function StatePanel({ title, description, action, tone = 'neutral' }: { title: string; description: string; action?: ReactNode; tone?: 'neutral' | 'warning' | 'danger'; }) {
    return <Surface role={tone === 'danger' ? 'alert' : 'status'} className={`p-6 text-center state-${tone}`}>
        <h2 className="text-lg font-bold">{title}</h2><p className="mx-auto mt-2 max-w-xl text-sm text-secondary">{description}</p>{action && <div className="mt-5">{action}</div>}
    </Surface>;
}
