export interface User {
    user_id: string;
    name: string;
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
    target_savings: string;
    savings: string;
    commitments: string;
    others: string;
    spending: string;
    allocated: string;
    balance: string;
}

export interface Planning {
    planning_id: string;
    month: number;
    year: number;
    salary: string;
    saving_rate: string;
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
