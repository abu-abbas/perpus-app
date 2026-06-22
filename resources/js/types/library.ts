export interface Category {
    id: number;
    name: string;
    description: string | null;
    created_at?: string;
}

export type ItemType = 'book' | 'magazine' | 'dvd';

export interface Item {
    id: number;
    category_id: number;
    category?: Category;
    type: ItemType;
    title: string;
    author: string;
    publisher: string | null;
    year: number;
    code: string;
    total_stock: number;
    available_stock: number;
    cover_image_url: string | null;
    attributes: Record<string, any> | null;
    display_info: Record<string, any> | null;
    late_fee_per_day: number;
    created_at?: string;
}

export interface Member {
    id: number;
    member_number: string;
    full_name: string;
    identity_number: string;
    phone: string | null;
    address: string | null;
    join_date: string;
    status: 'active' | 'inactive' | 'suspended';
    created_at?: string;
}

export interface Loan {
    id: number;
    member?: Member;
    item?: Item;
    librarian?: {
        id: number;
        name: string;
        email: string;
    };
    loan_date: string;
    due_date: string;
    return_date: string | null;
    status: 'borrowed' | 'returned' | 'overdue' | 'lost';
    is_overdue: boolean;
    days_late: number;
    fine?: Fine | null;
    created_at?: string;
}

export interface Fine {
    id: number;
    loan_id: number;
    loan?: Loan;
    amount: number;
    reason: 'late' | 'damaged' | 'lost';
    paid_status: 'unpaid' | 'paid';
    paid_date: string | null;
    created_at?: string;
}

export interface DashboardSummary {
    total_items: number;
    total_members: number;
    active_loans: number;
    overdue_loans: number;
    total_fines_collected: number;
    total_fines_unpaid: number;
    recent_loans: Loan[];
    popular_items: Array<{
        item: Item;
        loans_count: number;
    }>;
}

export interface ApiResponse<T> {
    data: T;
}

export interface ApiPaginatedResponse<T> {
    data: T[];
    links: {
        first: string | null;
        last: string | null;
        prev: string | null;
        next: string | null;
    };
    meta: {
        current_page: number;
        from: number | null;
        last_page: number;
        path: string;
        per_page: number;
        to: number | null;
        total: number;
    };
}
