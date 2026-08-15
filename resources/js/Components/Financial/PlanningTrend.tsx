import FinancialNumber from '@/Components/UI/FinancialNumber';
import { Planning } from '@/types';
import { magnitudeWidth, signedMoney } from '@/utils/financialPresentation';
import FinancialChartPanel from './FinancialChartPanel';

export default function PlanningTrend({ plannings, selectedId }: { plannings: Planning[]; selectedId: string }) {
    if (plannings.length < 2) return null;
    const ordered = [...plannings].sort((left, right) => (left.year - right.year) || (left.month - right.month));
    const maximum = ordered.reduce((max, planning) => {
        const values = [planning.salary, planning.totals.allocated, planning.totals.balance].map((value) => signedMoney(value) ?? 0n);
        return values.reduce((innerMax, value) => value > innerMax ? value : innerMax, max);
    }, 0n);

    const visual = <div className="trend-bars" aria-hidden="true">{ordered.map((planning) => <div key={planning.planning_id} className={planning.planning_id === selectedId ? 'is-selected' : ''}>
        <div className="trend-series"><span className="trend-income" style={{ height: `${magnitudeWidth(planning.salary, maximum)}%` }} /><span className="trend-allocated" style={{ height: `${magnitudeWidth(planning.totals.allocated, maximum)}%` }} /><span className="trend-remaining" style={{ height: `${magnitudeWidth(planning.totals.balance, maximum)}%` }} /></div><span>{planning.name}</span>
    </div>)}</div>;

    return <FinancialChartPanel title="Planned trend" description="Monthly income, total planned allocation, and remaining planned balance across available periods." visual={visual}>
        <table aria-label="Planned trend exact values" className="financial-table"><thead><tr><th scope="col">Period</th><th scope="col">Income</th><th scope="col">Allocated</th><th scope="col">Remaining</th></tr></thead><tbody>{ordered.map((planning) => <tr key={planning.planning_id} className={planning.planning_id === selectedId ? 'is-selected' : ''}><th scope="row">{planning.name}{planning.planning_id === selectedId ? ' (selected)' : ''}</th><td><FinancialNumber value={planning.salary} /></td><td><FinancialNumber value={planning.totals.allocated} /></td><td><FinancialNumber value={planning.totals.balance} /></td></tr>)}</tbody></table>
    </FinancialChartPanel>;
}
