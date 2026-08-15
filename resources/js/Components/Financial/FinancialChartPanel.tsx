import { PropsWithChildren, ReactNode } from 'react';
import Surface from '@/Components/UI/Surface';

export default function FinancialChartPanel({ title, description, visual, children }: PropsWithChildren<{ title: string; description: string; visual?: ReactNode }>) {
    return <Surface className="financial-chart-panel p-5">
        <div className="mb-5"><h2 className="text-lg font-bold">{title}</h2><p className="mt-1 text-sm text-secondary">{description}</p></div>
        {visual ?? <p role="status" className="chart-fallback">Visual unavailable. Exact values remain below.</p>}
        <div className="mt-5 overflow-x-auto">{children}</div>
    </Surface>;
}
