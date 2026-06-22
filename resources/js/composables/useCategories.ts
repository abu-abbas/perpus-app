import { useQuery, useMutation, useQueryClient } from '@tanstack/vue-query';
import axios from '@/lib/axios';
import type { Category, ApiResponse } from '@/types';
import { toast } from 'vue-sonner';

/**
 * Composable untuk modul Kategori menggunakan TanStack Vue Query.
 */
export function useCategories() {
    const queryClient = useQueryClient();

    // Query daftar kategori
    const categoriesQuery = useQuery({
        queryKey: ['categories'],
        queryFn: async () => {
            const response = await axios.get<ApiResponse<Category[]>>('/categories');
            return response.data.data;
        },
    });

    // Mutation tambah kategori
    const createCategoryMutation = useMutation({
        mutationFn: async (data: { name: string; description: string | null }) => {
            const response = await axios.post<ApiResponse<Category>>('/categories', data);
            return response.data.data;
        },
        onSuccess: () => {
            queryClient.invalidateQueries({ queryKey: ['categories'] });
            toast.success('Kategori berhasil ditambahkan.');
        },
    });

    // Mutation update kategori
    const updateCategoryMutation = useMutation({
        mutationFn: async ({ id, data }: { id: number; data: { name: string; description: string | null } }) => {
            const response = await axios.put<ApiResponse<Category>>(`/categories/${id}`, data);
            return response.data.data;
        },
        onSuccess: () => {
            queryClient.invalidateQueries({ queryKey: ['categories'] });
            toast.success('Kategori berhasil diperbarui.');
        },
    });

    // Mutation hapus kategori (admin only)
    const deleteCategoryMutation = useMutation({
        mutationFn: async (id: number) => {
            const response = await axios.delete(`/categories/${id}`);
            return response.data;
        },
        onSuccess: () => {
            queryClient.invalidateQueries({ queryKey: ['categories'] });
            toast.success('Kategori berhasil dihapus.');
        },
    });

    return {
        categories: categoriesQuery.data,
        isLoading: categoriesQuery.isLoading,
        isError: categoriesQuery.isError,
        refetch: categoriesQuery.refetch,
        createCategory: createCategoryMutation.mutateAsync,
        isCreating: createCategoryMutation.isPending,
        updateCategory: updateCategoryMutation.mutateAsync,
        isUpdating: updateCategoryMutation.isPending,
        deleteCategory: deleteCategoryMutation.mutateAsync,
        isDeleting: deleteCategoryMutation.isPending,
    };
}
