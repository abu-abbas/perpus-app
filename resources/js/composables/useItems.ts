import { useQuery, useMutation, useQueryClient } from '@tanstack/vue-query';
import axios from '@/lib/axios';
import type { Item, ApiPaginatedResponse, ApiResponse } from '@/types';
import { toast } from 'vue-sonner';
import { ref, computed } from 'vue';

interface UseItemsFilters {
    search?: string;
    type?: string;
    category_id?: string;
    page?: number;
    per_page?: number;
}

/**
 * Composable untuk modul Item menggunakan TanStack Vue Query.
 */
export function useItems(filters = ref<UseItemsFilters>({ page: 1 })) {
    const queryClient = useQueryClient();

    // Query daftar item terpaginasi dengan filter
    const itemsQuery = useQuery({
        queryKey: ['items', filters],
        queryFn: async () => {
            const params = { ...filters.value };
            // Hapus parameter kosong
            Object.keys(params).forEach(key => {
                if (params[key as keyof UseItemsFilters] === '' || params[key as keyof UseItemsFilters] === undefined) {
                    delete params[key as keyof UseItemsFilters];
                }
            });

            const response = await axios.get<ApiPaginatedResponse<Item>>('/items', { params });
            return response.data;
        },
    });

    // Mutation tambah item (mendukung upload cover image)
    const createItemMutation = useMutation({
        mutationFn: async (formData: FormData) => {
            const response = await axios.post<ApiResponse<Item>>('/items', formData, {
                headers: {
                    'Content-Type': 'multipart/form-data',
                },
            });
            return response.data.data;
        },
        onSuccess: () => {
            queryClient.invalidateQueries({ queryKey: ['items'] });
            toast.success('Item berhasil ditambahkan.');
        },
    });

    // Mutation update item (mendukung upload cover image baru via method spoofing)
    const updateItemMutation = useMutation({
        mutationFn: async ({ id, formData }: { id: number; formData: FormData }) => {
            // Karena upload file di PHP disarankan menggunakan POST dengan spoofing _method=PUT
            formData.append('_method', 'PUT');
            const response = await axios.post<ApiResponse<Item>>(`/items/${id}`, formData, {
                headers: {
                    'Content-Type': 'multipart/form-data',
                },
            });
            return response.data.data;
        },
        onSuccess: () => {
            queryClient.invalidateQueries({ queryKey: ['items'] });
            toast.success('Item berhasil diperbarui.');
        },
    });

    // Mutation hapus item (admin only)
    const deleteItemMutation = useMutation({
        mutationFn: async (id: number) => {
            const response = await axios.delete(`/items/${id}`);
            return response.data;
        },
        onSuccess: () => {
            queryClient.invalidateQueries({ queryKey: ['items'] });
            toast.success('Item berhasil dihapus.');
        },
    });

    // Mutation import excel/csv
    const importItemsMutation = useMutation({
        mutationFn: async (file: File) => {
            const formData = new FormData();
            formData.append('file', file);
            const response = await axios.post('/items/import', formData, {
                headers: {
                    'Content-Type': 'multipart/form-data',
                },
            });
            return response.data;
        },
        onSuccess: (data) => {
            queryClient.invalidateQueries({ queryKey: ['items'] });
            toast.success(data.message || 'Import data item berhasil dilakukan.');
        },
    });

    /**
     * Download export file (PDF atau Excel).
     */
    function exportItems(format: 'pdf' | 'excel') {
        const downloadUrl = `/api/items/export/${format}`;
        window.open(downloadUrl, '_blank');
    }

    return {
        itemsResponse: itemsQuery.data,
        items: computed(() => itemsQuery.data.value?.data || []),
        isLoading: itemsQuery.isLoading,
        isError: itemsQuery.isError,
        refetch: itemsQuery.refetch,
        createItem: createItemMutation.mutateAsync,
        isCreating: createItemMutation.isPending,
        updateItem: updateItemMutation.mutateAsync,
        isUpdating: updateItemMutation.isPending,
        deleteItem: deleteItemMutation.mutateAsync,
        isDeleting: deleteItemMutation.isPending,
        importItems: importItemsMutation.mutateAsync,
        isImporting: importItemsMutation.isPending,
        exportItems,
    };
}
