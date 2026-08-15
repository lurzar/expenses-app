import { formatMYR } from '@/utils/money';

export default function FinancialNumber({ value, className = '', unavailableLabel = 'Unavailable' }: { value?: string | null; className?: string; unavailableLabel?: string; }) {
    if (value === null || value === undefined) return <span className={`ui-financial-number ${className}`} aria-label={unavailableLabel}>RM —</span>;
    return <span className={`ui-financial-number ${className}`}>{formatMYR(value)}</span>;
}
