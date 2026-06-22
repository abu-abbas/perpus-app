import { useQuery, useMutation, useQueryClient } from '@tanstack/vue-query';
import axios from '@/lib/axios';
import type { Member, ApiPaginatedResponse, ApiResponse } from '@/types';
import { toast } from 'vue-sonner';
import { ref, computed } from 'vue';

interface UseMembersFilters {
    search?: string;
    status?: string;
    page?: number;
    per_page?: number;
}

/**
 * Composable untuk modul Anggota (Members) menggunakan TanStack Vue Query.
 */
export function useMembers(filters = ref<UseMembersFilters>({ page: 1 })) {
    const queryClient = useQueryClient();

    // Query daftar anggota terpaginasi dengan filter
    const membersQuery = useQuery({
        queryKey: ['members', filters],
        queryFn: async () => {
            const params = { ...filters.value };
            // Hapus parameter kosong
            Object.keys(params).forEach(key => {
                if (params[key as keyof UseMembersFilters] === '' || params[key as keyof UseMembersFilters] === undefined) {
                    delete params[key as keyof UseMembersFilters];
                }
            });

            const response = await axios.get<ApiPaginatedResponse<Member>>('/members', { params });
            return response.data;
        },
    });

    // Mutation tambah anggota
    const createMemberMutation = useMutation({
        mutationFn: async (data: { full_name: string; identity_number: string; phone: string | null; address: string | null; join_date?: string; status?: string }) => {
            const response = await axios.post<ApiResponse<Member>>('/members', data);
            return response.data.data;
        },
        onSuccess: () => {
            queryClient.invalidateQueries({ queryKey: ['members'] });
            toast.success('Anggota berhasil didaftarkan.');
        },
    });

    // Mutation update anggota
    const updateMemberMutation = useMutation({
        mutationFn: async ({ id, data }: { id: number; data: { full_name: string; identity_number: string; phone: string | null; address: string | null; status: string } }) => {
            const response = await axios.put<ApiResponse<Member>>(`/members/${id}`, data);
            return response.data.data;
        },
        onSuccess: () => {
            queryClient.invalidateQueries({ queryKey: ['members'] });
            toast.success('Data anggota berhasil diperbarui.');
        },
    });

    // Mutation hapus anggota (admin only)
    const deleteMemberMutation = useMutation({
        mutationFn: async (id: number) => {
            const response = await axios.delete(`/members/${id}`);
            return response.data;
        },
        onSuccess: () => {
            queryClient.invalidateQueries({ queryKey: ['members'] });
            toast.success('Anggota berhasil dihapus.');
        },
    });

    return {
        membersResponse: membersQuery.data,
        members: computed(() => membersQuery.data.value?.data || []),
        isLoading: membersQuery.isLoading,
        isError: membersQuery.isError,
        refetch: membersQuery.refetch,
        createMember: createMemberMutation.mutateAsync,
        isCreating: createMemberMutation.isPending,
        updateMember: updateMemberMutation.mutateAsync,
        isUpdating: updateMemberMutation.isPending,
        deleteMember: deleteMemberMutation.mutateAsync,
        isDeleting: deleteMemberMutation.isPending,
    };
}
