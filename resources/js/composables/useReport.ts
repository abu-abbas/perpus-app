import { useQuery } from '@tanstack/vue-query';
import axios from '@/lib/axios';
import { ref } from 'vue';

interface UseReportFilters {
    date_from?: string;
    date_to?: string;
}

/**
 * Composable untuk Laporan Peminjaman (Reports) menggunakan TanStack Vue Query.
 */
export function useReport(filters = ref<UseReportFilters>({})) {
    const reportQuery = useQuery({
        queryKey: ['loanReport', filters],
        queryFn: async () => {
            const params = { ...filters.value };
            // Hapus parameter kosong
            Object.keys(params).forEach(key => {
                if (params[key as keyof UseReportFilters] === '' || params[key as keyof UseReportFilters] === undefined) {
                    delete params[key as keyof UseReportFilters];
                }
            });

            const response = await axios.get('/dashboard/loan-report', { params });
            return response.data.data;
        },
    });

    /**
     * Download export laporan peminjaman (PDF atau Excel).
     */
    function exportReport(format: 'pdf' | 'excel') {
        const params = new URLSearchParams();
        if (filters.value.date_from) params.append('date_from', filters.value.date_from);
        if (filters.value.date_to) params.append('date_to', filters.value.date_to);

        const downloadUrl = `/api/dashboard/loan-report/export/${format}?${params.toString()}`;
        window.open(downloadUrl, '_blank');
    }

    return {
        reportData: reportQuery.data,
        isLoading: reportQuery.isLoading,
        isError: reportQuery.isError,
        refetch: reportQuery.refetch,
        exportReport,
    };
}
