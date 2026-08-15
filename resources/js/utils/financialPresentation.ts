import { formatSen, parseMoney } from './money';

export interface PresentationRatio {
    basisPoints: bigint | null;
    label: string | null;
    width: number;
}

export function signedMoney(value: string | null | undefined): bigint | null {
    if (!value) return null;
    const negative = value.startsWith('-');
    const parsed = parseMoney(negative ? value.slice(1) : value);

    return parsed === null ? null : (negative ? -parsed : parsed);
}

export function presentationRatio(value: string | null | undefined, total: string | null | undefined): PresentationRatio {
    const numerator = signedMoney(value);
    const denominator = signedMoney(total);

    if (numerator === null || denominator === null || denominator <= 0n || numerator < 0n) {
        return { basisPoints: null, label: null, width: 0 };
    }

    const basisPoints = ((numerator * 10_000n) + (denominator / 2n)) / denominator;
    const percent = `${basisPoints / 100n}.${(basisPoints % 100n).toString().padStart(2, '0')}`
        .replace(/\.00$/, '')
        .replace(/(\.\d)0$/, '$1');

    return {
        basisPoints,
        label: `${percent}%`,
        width: Number(basisPoints > 10_000n ? 10_000n : basisPoints) / 100,
    };
}

export function moneyDifference(value: string, reference: string): string | null {
    const left = signedMoney(value);
    const right = signedMoney(reference);
    return left === null || right === null ? null : formatSen(left - right);
}

export function magnitudeWidth(value: string, maximum: bigint): number {
    const parsed = signedMoney(value);
    if (parsed === null || parsed <= 0n || maximum <= 0n) return 0;

    return Number(((parsed * 10_000n) / maximum)) / 100;
}
