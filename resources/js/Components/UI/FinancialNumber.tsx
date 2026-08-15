import { formatMYR } from '@/utils/money';

export default function FinancialNumber({ value, className = '', unavailableLabel = 'Unavailable' }: { value?: string | null; className?: string; unavailableLabel?: string; }) {
    if (value === null || value === undefined) return <span className={`ui-financial-number ${className}`} aria-label={unavailableLabel}>RM —</span>;
    const negative = value.startsWith('-');
    const formatted = formatMYR(negative ? value.slice(1) : value);
    const display = negative && formatted !== 'RM —' ? `−${formatted}` : formatted;

    return <span className={`ui-financial-number ${className}`}>{display}</span>;
}
