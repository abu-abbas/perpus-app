import { useQuery, useMutation, useQueryClient } from '@tanstack/vue-query';
import axios from '@/lib/axios';
import type { Fine, ApiPaginatedResponse, ApiResponse } from '@/types';
import { toast } from 'vue-sonner';
import { ref, computed } from 'vue';

interface UseFinesFilters {
    paid_status?: string;
    page?: number;
    per_page?: number;
}

/**
 * Composable untuk modul Denda (Fines) menggunakan TanStack Vue Query.
 */
export function useFines(filters = ref<UseFinesFilters>({ page: 1 })) {
    const queryClient = useQueryClient();

    // Query daftar denda terpaginasi dengan filter
    const finesQuery = useQuery({
        queryKey: ['fines', filters],
        queryFn: async () => {
            const params = { ...filters.value };
            // Hapus parameter kosong
            Object.keys(params).forEach(key => {
                if (params[key as keyof UseFinesFilters] === '' || params[key as keyof UseFinesFilters] === undefined) {
                    delete params[key as keyof UseFinesFilters];
                }
            });

            const response = await axios.get<ApiPaginatedResponse<Fine>>('/fines', { params });
            return response.data;
        },
    });

    // Mutation bayar denda (lunas)
    const payFineMutation = useMutation({
        mutationFn: async (id: number) => {
            const response = await axios.post<ApiResponse<Fine>>(`/fines/${id}/pay`);
            return response.data.data;
        },
        onSuccess: () => {
            queryClient.invalidateQueries({ queryKey: ['fines'] });
            queryClient.invalidateQueries({ queryKey: ['loans'] });
            queryClient.invalidateQueries({ queryKey: ['members'] });
            queryClient.invalidateQueries({ queryKey: ['dashboardSummary'] });
            toast.success('Denda berhasil ditandai lunas.');
        },
    });

    return {
        finesResponse: finesQuery.data,
        fines: computed(() => finesQuery.data.value?.data || []),
        isLoading: finesQuery.isLoading,
        isError: finesQuery.isError,
        refetch: finesQuery.refetch,
        payFine: payFineMutation.mutateAsync,
        isPaying: payFineMutation.isPending,
    };
}
