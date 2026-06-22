import { useQuery, useMutation, useQueryClient } from '@tanstack/vue-query';
import axios from '@/lib/axios';
import type { Loan, ApiPaginatedResponse, ApiResponse } from '@/types';
import { toast } from 'vue-sonner';
import { ref, computed } from 'vue';

interface UseLoansFilters {
    search?: string;
    status?: string;
    member_id?: number;
    page?: number;
    per_page?: number;
}

/**
 * Composable untuk modul Peminjaman (Loans) menggunakan TanStack Vue Query.
 */
export function useLoans(filters = ref<UseLoansFilters>({ page: 1 })) {
    const queryClient = useQueryClient();

    // Query daftar peminjaman terpaginasi dengan filter
    const loansQuery = useQuery({
        queryKey: ['loans', filters],
        queryFn: async () => {
            const params = { ...filters.value };
            // Hapus parameter kosong
            Object.keys(params).forEach(key => {
                if (params[key as keyof UseLoansFilters] === '' || params[key as keyof UseLoansFilters] === undefined) {
                    delete params[key as keyof UseLoansFilters];
                }
            });

            const response = await axios.get<ApiPaginatedResponse<Loan>>('/loans', { params });
            return response.data;
        },
    });

    // Mutation buat peminjaman baru (Pinjam)
    const createLoanMutation = useMutation({
        mutationFn: async (data: { member_id: number; item_id: number }) => {
            const response = await axios.post<ApiResponse<Loan>>('/loans', data);
            return response.data.data;
        },
        onSuccess: () => {
            queryClient.invalidateQueries({ queryKey: ['loans'] });
            queryClient.invalidateQueries({ queryKey: ['items'] });
            toast.success('Peminjaman berhasil diproses.');
        },
    });

    // Mutation pengembalian item (Kembali)
    const returnLoanMutation = useMutation({
        mutationFn: async (id: number) => {
            const response = await axios.post<ApiResponse<Loan>>(`/loans/${id}/return`);
            return response.data.data;
        },
        onSuccess: (data) => {
            queryClient.invalidateQueries({ queryKey: ['loans'] });
            queryClient.invalidateQueries({ queryKey: ['items'] });
            queryClient.invalidateQueries({ queryKey: ['fines'] });
            queryClient.invalidateQueries({ queryKey: ['dashboardSummary'] });

            // Jika ada denda yang dibuat otomatis karena keterlambatan
            if (data.fine) {
                toast.warning(`Item berhasil dikembalikan, tetapi terlambat ${data.days_late} hari. Dikenakan denda sebesar Rp${data.fine.amount.toLocaleString('id-ID')}.`);
            } else {
                toast.success('Item berhasil dikembalikan tepat waktu.');
            }
        },
    });

    return {
        loansResponse: loansQuery.data,
        loans: computed(() => loansQuery.data.value?.data || []),
        isLoading: loansQuery.isLoading,
        isError: loansQuery.isError,
        refetch: loansQuery.refetch,
        createLoan: createLoanMutation.mutateAsync,
        isCreating: createLoanMutation.isPending,
        returnLoan: returnLoanMutation.mutateAsync,
        isReturning: returnLoanMutation.isPending,
    };
}
