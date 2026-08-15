import FinancialNumber from './FinancialNumber';
import Surface from './Surface';

export default function MetricCard({ label, value, context, hero = false, className = '', tone = 'neutral' }: { label: string; value?: string | null; context?: string; hero?: boolean; className?: string; tone?: 'neutral' | 'danger'; }) {
    return <Surface className={`p-5 ${hero ? 'ui-surface-raised' : ''} ${tone === 'danger' ? 'state-danger' : ''} ${className}`}>
        <p className="text-sm font-semibold text-secondary">{label}</p>
        <p className={`${hero ? 'text-3xl sm:text-4xl' : 'text-2xl'} mt-2 font-bold`}><FinancialNumber value={value} /></p>
        {context && <p className="mt-2 flex items-start gap-2 text-sm text-secondary">{tone === 'danger' && <span aria-hidden="true">⚠</span>}<span>{context}</span></p>}
    </Surface>;
}
