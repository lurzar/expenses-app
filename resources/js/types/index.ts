export interface User {
    id: string;
    user_id: string;
    name: string;
    slug: string;
    email: string;
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
}

export interface SectionItem {
    item: string;
    amount: string;
}

export interface PlanningSection {
    savings?: SectionItem[];
    commitments?: SectionItem[];
    others?: SectionItem[];
}

export interface PlanningTotals {
    savings?: number;
    commitments?: number;
    others?: number;
}

export interface Planning {
    id: string;
    planning_id: string;
    user_id: string;
    month: string;
    year: string;
    salary: number;
    slug: string;
    sections: PlanningSection;
    totals: PlanningTotals;
    name: string;      // computed attribute
    spending: string;  // computed attribute
    created_at: string;
    updated_at: string;
}

export interface PageProps {
    auth: {
        user: User | null;
    };
    flash: {
        message: string | null;
        success: string | null;
        error: string | null;
    };
    locale?: string;
    translations?: Record<string, Record<string, unknown>>;
    [key: string]: unknown;
}
