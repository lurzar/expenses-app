import { describe, expect, it } from 'vitest';
import { route } from './utils/route';

describe('Admin route contract', () => {
    it('generates the server-protected Admin landing URL', () => {
        expect(route('admin.index')).toBe('/admin');
    });
});
