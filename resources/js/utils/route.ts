import { route as ziggyRoute } from 'ziggy-js';
import { Ziggy } from '@/ziggy';

// Re-export route with Ziggy config bound
// eslint-disable-next-line @typescript-eslint/no-explicit-any
export function route(name: string, params?: any, absolute: boolean = false): string {
    return ziggyRoute(name, params, absolute, Ziggy as any) as string;
}
