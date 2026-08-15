import FinancialNumber from '@/Components/UI/FinancialNumber';
import MetricCard from '@/Components/UI/MetricCard';
import Surface from '@/Components/UI/Surface';
import { Planning, SectionItem } from '@/types';
import { formatMYR, parseMoney } from '@/utils/money';
import { magnitudeWidth, moneyDifference, presentationRatio, signedMoney } from '@/utils/financialPresentation';
import FinancialChartPanel from './FinancialChartPanel';

const percentage = (value: string, total: string) => presentationRatio(value, total).label ?? 'Percentage unavailable';
const availableMoney = (value: string): string | null => signedMoney(value) === null ? null : value;

function ExactTable({ label, rows }: { label: string; rows: Array<{ label: string; value: string | null; percentage?: string }> }) {
    return <table aria-label={label} className="financial-table">
        <thead><tr><th scope="col">Figure</th><th scope="col">Exact value</th><th scope="col">Percentage</th></tr></thead>
        <tbody>{rows.map((row) => <tr key={row.label}><th scope="row">{row.label}</th><td><FinancialNumber value={row.value} /></td><td>{row.percentage ?? 'Not applicable'}</td></tr>)}</tbody>
    </table>;
}

function AllocationComposition({ planning }: { planning: Planning }) {
    const rows = [
        { label: 'Savings allocation', value: planning.totals.savings, className: 'chart-savings' },
        { label: 'Commitments', value: planning.totals.commitments, className: 'chart-commitments' },
        { label: 'Other allocations', value: planning.totals.others, className: 'chart-others' },
    ];
    const unavailable = signedMoney(planning.totals.allocated) === null || rows.some((row) => signedMoney(row.value) === null);
    const empty = !unavailable && signedMoney(planning.totals.allocated) === 0n;
    const visual = unavailable
        ? <p className="chart-empty">Allocation comparison unavailable.</p>
        : empty
        ? <p className="chart-empty">No allocations added</p>
        : <div className="allocation-bar" aria-hidden="true">{rows.map((row) => <span key={row.label} className={row.className} style={{ width: `${presentationRatio(row.value, planning.totals.allocated).width}%` }} />)}</div>;

    return <FinancialChartPanel title="Allocation composition" description="Savings, commitments, and other allocations in a fixed category order." visual={visual}>
        <ExactTable label="Allocation composition exact values" rows={rows.map((row) => ({ ...row, value: availableMoney(row.value), percentage: percentage(row.value, planning.totals.allocated) }))} />
    </FinancialChartPanel>;
}

function IncomeAllocation({ planning }: { planning: Planning }) {
    const incomeValue = signedMoney(planning.salary);
    const allocatedValue = signedMoney(planning.totals.allocated);
    const balanceValue = signedMoney(planning.totals.balance);
    const unavailable = incomeValue === null || allocatedValue === null || balanceValue === null;
    const allocated = presentationRatio(planning.totals.allocated, planning.salary);
    const remaining = presentationRatio(planning.totals.balance, planning.salary);
    const zeroIncome = incomeValue === 0n;
    const negativeBalance = balanceValue !== null && balanceValue < 0n;
    const overage = negativeBalance ? planning.totals.balance.replace('-', '') : null;
    const visual = unavailable
        ? <p className="chart-empty">Income comparison unavailable.</p>
        : zeroIncome
        ? <p className="chart-empty">Income comparison unavailable because monthly income is zero.</p>
        : <div>{/* Exact text remains visible because the bar itself is decorative. */}<div className={`income-bar ${negativeBalance ? 'is-over-allocated' : ''}`} aria-hidden="true"><span className="chart-allocated" style={{ width: `${allocated.width}%` }} />{!negativeBalance && <span className="chart-remaining" style={{ width: `${remaining.width}%` }} />}</div>{negativeBalance && <><span className="over-allocation-marker" aria-hidden="true" /><p className="mt-2 text-sm font-semibold">Over allocated by {formatMYR(overage)}</p></>}</div>;

    return <FinancialChartPanel title="Income and allocation" description={unavailable ? 'Income comparison unavailable.' : negativeBalance ? `Over allocated by ${formatMYR(overage)}.` : 'How the total planned allocation and remaining balance compare with monthly income.'} visual={visual}>
        <ExactTable label="Income and allocation exact values" rows={[
            { label: 'Monthly income', value: availableMoney(planning.salary) },
            { label: 'Total planned allocation', value: availableMoney(planning.totals.allocated), percentage: allocated.label ?? 'Percentage unavailable' },
            { label: negativeBalance ? 'Over allocation' : 'Remaining planned balance', value: availableMoney(negativeBalance ? overage! : planning.totals.balance), percentage: remaining.label ?? 'Percentage unavailable' },
        ]} />
    </FinancialChartPanel>;
}

function SavingsTarget({ planning }: { planning: Planning }) {
    const savingsValue = signedMoney(planning.totals.savings);
    const targetValue = signedMoney(planning.totals.target_savings);
    const unavailable = savingsValue === null || targetValue === null;
    const progress = presentationRatio(planning.totals.savings, planning.totals.target_savings);
    const zeroTarget = targetValue === 0n;
    const difference = moneyDifference(planning.totals.savings, planning.totals.target_savings);
    const above = difference !== null && difference.startsWith('-') === false && difference !== '0.00';
    const below = difference !== null && difference.startsWith('-');
    const absoluteDifference = difference?.replace('-', '') ?? null;
    const differenceLabel = unavailable ? 'Comparison unavailable' : above ? 'Above target by' : below ? 'Below target by' : 'Target difference';
    const directionText = above ? 'Above savings target by' : below ? 'Below savings target by' : null;
    const visual = unavailable
        ? <p className="chart-empty">Savings target comparison unavailable.</p>
        : zeroTarget
        ? <p className="chart-empty">{signedMoney(planning.totals.savings) === 0n ? 'No savings target set' : 'Target is zero; percentage unavailable'}</p>
        : <div><div className="target-track" aria-hidden="true"><span style={{ width: `${progress.width}%` }} /></div>{directionText && <p className="mt-2 text-sm font-semibold">{directionText} {formatMYR(absoluteDifference)}</p>}</div>;

    return <FinancialChartPanel title="Savings target" description="Savings allocation compared with the advisory target." visual={visual}>
        <ExactTable label="Savings target exact values" rows={[
            { label: 'Savings target', value: availableMoney(planning.totals.target_savings) },
            { label: 'Savings allocation', value: availableMoney(planning.totals.savings), percentage: progress.label ?? 'Percentage unavailable' },
            { label: differenceLabel, value: absoluteDifference },
        ]} />
    </FinancialChartPanel>;
}

export function AllocationSection({ title, total, items = [] }: { title: string; total: string; items?: SectionItem[] }) {
    const maximum = items.reduce((max, item) => {
        const amount = parseMoney(item.amount) ?? 0n;
        return amount > max ? amount : max;
    }, 0n);

    return <Surface className="allocation-section p-5">
        <div className="flex items-start justify-between gap-4"><div><h2 className="text-lg font-bold">{title}</h2><p className="mt-1 text-sm text-secondary">{items.length} {items.length === 1 ? 'item' : 'items'}</p></div><FinancialNumber value={total} className="font-bold" /></div>
        {items.length === 0 ? <p className="mt-5 text-sm text-secondary">No items added.</p> : <ul className="mt-5 space-y-4">{items.map((item, index) => <li key={`${item.item}-${index}`}>
            <div className="flex items-start justify-between gap-4 text-sm"><span className="min-w-0 break-words">{item.item}</span><FinancialNumber value={item.amount} className="shrink-0 font-semibold" /></div>
            <div className="magnitude-track mt-2" aria-hidden="true"><span style={{ width: `${magnitudeWidth(item.amount, maximum)}%` }} /></div>
        </li>)}</ul>}
    </Surface>;
}

export default function PlanningSummary({ planning, showSections = true }: { planning: Planning; showSections?: boolean }) {
    const targetProgress = percentage(planning.totals.savings, planning.totals.target_savings);
    const balance = signedMoney(planning.totals.balance);
    const overAllocated = balance !== null && balance < 0n;
    const heroContext = overAllocated
        ? `Over allocated by ${formatMYR(planning.totals.balance.replace('-', ''))}`
        : balance === null ? `${planning.name} balance unavailable` : `${planning.name} monthly plan`;

    return <div className="space-y-6">
        <MetricCard hero label="Remaining planned balance" value={planning.totals.balance} context={heroContext} tone={overAllocated ? 'danger' : 'neutral'} className="financial-hero" />
        <div className="metric-grid">
            <MetricCard label="Monthly income" value={planning.salary} context={planning.name} />
            <MetricCard label="Savings allocation" value={planning.totals.savings} context={`Target ${formatMYR(planning.totals.target_savings)} · ${targetProgress}`} />
            <MetricCard label="Commitments" value={planning.totals.commitments} />
            <MetricCard label="Other allocations" value={planning.totals.others} />
            <MetricCard label="Total planned allocation" value={planning.totals.allocated} context={`Planned spending ${formatMYR(planning.totals.spending)}`} className="metric-rollup" />
        </div>
        <div className="chart-grid"><AllocationComposition planning={planning} /><div className="space-y-6"><IncomeAllocation planning={planning} /><SavingsTarget planning={planning} /></div></div>
        {showSections && <div className="allocation-grid">
            <AllocationSection title="Savings allocation" total={planning.totals.savings} items={planning.sections?.savings} />
            <AllocationSection title="Commitments" total={planning.totals.commitments} items={planning.sections?.commitments} />
            <AllocationSection title="Other allocations" total={planning.totals.others} items={planning.sections?.others} />
        </div>}
    </div>;
}
