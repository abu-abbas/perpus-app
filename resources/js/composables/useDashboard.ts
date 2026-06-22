import { useQuery } from '@tanstack/vue-query';
import axios from '@/lib/axios';
import type { DashboardSummary, ApiResponse } from '@/types';

/**
 * Composable untuk ringkasan dashboard menggunakan TanStack Vue Query.
 */
export function useDashboard() {
    const dashboardQuery = useQuery({
        queryKey: ['dashboardSummary'],
        queryFn: async () => {
            const response = await axios.get<ApiResponse<DashboardSummary>>('/dashboard/summary');
            return response.data.data;
        },
    });

    return {
        summary: dashboardQuery.data,
        isLoading: dashboardQuery.isLoading,
        isError: dashboardQuery.isError,
        refetch: dashboardQuery.refetch,
    };
}
