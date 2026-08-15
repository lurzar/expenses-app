const MONEY_PATTERN = /^(?:0|[1-9]\d{0,11})(?:\.\d{1,2})?$/;
const RATE_PATTERN = /^(?:0|[1-9]\d?|100)(?:\.\d{1,2})?$/;
const MAX_SEN = 99_999_999_999_999n;

export interface PlanningPreviewInput {
    income: string;
    savingRate: string;
    savings: string[];
    commitments: string[];
    others: string[];
}

export interface PlanningPreview {
    targetSavings: string;
    savings: string;
    commitments: string;
    others: string;
    spending: string;
    allocated: string;
    balance: string;
}

export function parseMoney(value: string): bigint | null {
    if (!MONEY_PATTERN.test(value)) return null;

    const [whole, fraction = ''] = value.split('.');
    const sen = (BigInt(whole) * 100n) + BigInt(fraction.padEnd(2, '0'));

    return sen <= MAX_SEN ? sen : null;
}

function parseRate(value: string): bigint | null {
    if (!RATE_PATTERN.test(value)) return null;

    const [whole, fraction = ''] = value.split('.');
    const basisPoints = (BigInt(whole) * 100n) + BigInt(fraction.padEnd(2, '0'));

    return basisPoints <= 10_000n ? basisPoints : null;
}

export function formatSen(sen: bigint): string {
    const sign = sen < 0n ? '-' : '';
    const absolute = sen < 0n ? -sen : sen;

    return `${sign}${absolute / 100n}.${(absolute % 100n).toString().padStart(2, '0')}`;
}

export function formatMYR(value: string): string {
    const negative = value.startsWith('-');
    const unsigned = negative ? value.slice(1) : value;
    const sen = parseMoney(unsigned);

    if (sen === null) return 'RM —';

    const [whole, fraction] = formatSen(sen).split('.');
    const grouped = whole.replace(/\B(?=(\d{3})+(?!\d))/g, ',');

    return `RM ${negative ? '-' : ''}${grouped}.${fraction}`;
}

function sum(values: string[]): bigint {
    return values.reduce((total, value) => total + (parseMoney(value) ?? 0n), 0n);
}

export function calculatePlanningPreview(input: PlanningPreviewInput): PlanningPreview {
    const income = parseMoney(input.income) ?? 0n;
    const rate = parseRate(input.savingRate) ?? 0n;
    const savings = sum(input.savings);
    const commitments = sum(input.commitments);
    const others = sum(input.others);
    const spending = commitments + others;
    const allocated = savings + spending;
    const targetSavings = ((income * rate) + 5_000n) / 10_000n;

    return {
        targetSavings: formatSen(targetSavings),
        savings: formatSen(savings),
        commitments: formatSen(commitments),
        others: formatSen(others),
        spending: formatSen(spending),
        allocated: formatSen(allocated),
        balance: formatSen(income - allocated),
    };
}
