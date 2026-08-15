import FinancialNumber from './FinancialNumber';
import Surface from './Surface';

export default function MetricCard({ label, value, context, hero = false, className = '' }: { label: string; value?: string | null; context?: string; hero?: boolean; className?: string; }) {
    return <Surface className={`p-5 ${hero ? 'ui-surface-raised' : ''} ${className}`}>
        <p className="text-sm font-semibold text-secondary">{label}</p>
        <p className={`${hero ? 'text-3xl sm:text-4xl' : 'text-2xl'} mt-2 font-bold`}><FinancialNumber value={value} /></p>
        {context && <p className="mt-2 text-sm text-secondary">{context}</p>}
    </Surface>;
}
