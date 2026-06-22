<script setup lang="ts">
import { Plus, Edit2, Trash2 } from '@lucide/vue';
import { toTypedSchema } from '@vee-validate/zod';
import { useForm, useField } from 'vee-validate';
import { ref } from 'vue';
import { z } from 'zod';

import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
    DialogFooter
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { useCategories } from '@/composables/useCategories';
import { useAuthStore } from '@/stores/auth';
import type { Category } from '@/types';


const authStore = useAuthStore();
const {
    categories,
    isLoading,
    isError,
    createCategory,
    isCreating,
    updateCategory,
    isUpdating,
    deleteCategory
} = useCategories();

const isDialogOpen = ref(false);
const editingCategory = ref<Category | null>(null);

// Skema Zod Validasi
const categorySchema = z.object({
    name: z.string().min(1, 'Nama kategori wajib diisi').max(100, 'Nama kategori maksimal 100 karakter'),
    description: z.string().max(255, 'Deskripsi maksimal 255 karakter').nullable().optional(),
});

const { handleSubmit, errors, resetForm, setValues } = useForm({
    validationSchema: toTypedSchema(categorySchema),
});

const { value: name } = useField<string>('name');
const { value: description } = useField<string | null>('description');

function openAddDialog() {
    editingCategory.value = null;
    resetForm();
    name.value = '';
    description.value = '';
    isDialogOpen.value = true;
}

function openEditDialog(category: Category) {
    editingCategory.value = category;
    resetForm();
    setValues({
        name: category.name,
        description: category.description,
    });
    isDialogOpen.value = true;
}

const onSubmit = handleSubmit(async (values: any) => {
    try {
        if (editingCategory.value) {
            await updateCategory({
                id: editingCategory.value.id,
                data: {
                    name: values.name,
                    description: values.description ?? null,
                },
            });
        } else {
            await createCategory({
                name: values.name,
                description: values.description ?? null,
            });
        }

        isDialogOpen.value = false;
    } catch (e) {
        console.error(e);
    }
});

async function handleDelete(id: number) {
    if (confirm('Apakah Anda yakin ingin menghapus kategori ini? Semua item di bawah kategori ini juga mungkin terpengaruh.')) {
        try {
            await deleteCategory(id);
        } catch (e) {
            console.error(e);
        }
    }
}
</script>

<template>
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div class="flex flex-col gap-1">
                <h1 class="text-2xl font-bold tracking-tight">Kategori</h1>
                <p class="text-sm text-slate-500 dark:text-slate-400">
                    Kelola kategori buku, majalah, dan DVD.
                </p>
            </div>
            <Button
                @click="openAddDialog"
                class="flex items-center gap-2 px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg shadow-sm transition-all duration-200"
            >
                <Plus class="w-4 h-4" />
                Tambah Kategori
            </Button>
        </div>

        <!-- Loader / Error States -->
        <div v-if="isLoading" class="p-8 text-center text-slate-500">
            Memuat data kategori...
        </div>
        <div v-else-if="isError" class="p-4 rounded-xl bg-red-50 dark:bg-red-950/20 text-red-600 dark:text-red-400 text-sm border border-red-200 dark:border-red-900/50">
            Gagal mengambil data kategori.
        </div>

        <!-- Categories Table -->
        <div v-else class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden transition-colors">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-sm">
                    <thead>
                        <tr class="border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/20 text-slate-500 font-semibold">
                            <th class="py-3.5 px-6">Nama</th>
                            <th class="py-3.5 px-6">Deskripsi</th>
                            <th class="py-3.5 px-6 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800/80">
                        <tr v-for="category in categories" :key="category.id" class="hover:bg-slate-50/40 dark:hover:bg-slate-800/10">
                            <td class="py-4 px-6 font-semibold">{{ category.name }}</td>
                            <td class="py-4 px-6 text-slate-500 dark:text-slate-400">
                                {{ category.description || '-' }}
                            </td>
                            <td class="py-4 px-6 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        @click="openEditDialog(category)"
                                        class="p-1.5 h-8 w-8 text-slate-500 hover:text-blue-600 dark:hover:text-blue-400 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-md transition-colors"
                                    >
                                        <Edit2 class="w-4 h-4" />
                                    </Button>
                                    <Button
                                        v-if="authStore.isAdmin"
                                        variant="ghost"
                                        size="icon"
                                        @click="handleDelete(category.id)"
                                        class="p-1.5 h-8 w-8 text-slate-500 hover:text-red-600 dark:hover:text-red-400 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-md transition-colors"
                                    >
                                        <Trash2 class="w-4 h-4" />
                                    </Button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="!categories?.length">
                            <td colspan="3" class="py-8 text-center text-slate-400">Kategori kosong. Silakan tambah kategori baru.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Add/Edit Dialog Modal -->
        <Dialog v-model:open="isDialogOpen">
            <DialogContent class="sm:max-w-md bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 transition-colors duration-300">
                <DialogHeader class="space-y-1">
                    <DialogTitle class="text-lg font-bold">
                        {{ editingCategory ? 'Perbarui Kategori' : 'Tambah Kategori Baru' }}
                    </DialogTitle>
                </DialogHeader>
                <form @submit.prevent="onSubmit" class="space-y-4">
                    <!-- Name Input -->
                    <div class="space-y-1.5">
                        <Label for="name" class="text-xs font-semibold text-slate-550 dark:text-slate-400 uppercase tracking-wider">Nama Kategori</Label>
                        <Input
                            id="name"
                            v-model="name"
                            type="text"
                            placeholder="Contoh: Novel, Sains, Teknologi"
                            class="block w-full px-3 py-2 border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 rounded-lg focus-visible:ring-blue-500 text-sm transition-all"
                            :class="[errors.name ? 'border-red-500 focus-visible:ring-red-500' : '']"
                        />
                        <p v-if="errors.name" class="text-xs text-red-600 dark:text-red-400">{{ errors.name }}</p>
                    </div>

                    <!-- Description Input -->
                    <div class="space-y-1.5">
                        <Label for="description" class="text-xs font-semibold text-slate-550 dark:text-slate-400 uppercase tracking-wider">Deskripsi</Label>
                        <textarea
                            id="description"
                            v-model="description"
                            rows="3"
                            placeholder="Deskripsi singkat kategori..."
                            class="block w-full px-3 py-2 border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 text-sm transition-all"
                            :class="[errors.description ? 'border-red-300 focus:border-red-500 focus:ring-red-500' : '']"
                        ></textarea>
                        <p v-if="errors.description" class="text-xs text-red-650 dark:text-red-400">{{ errors.description }}</p>
                    </div>

                    <!-- Buttons -->
                    <DialogFooter class="flex items-center justify-end gap-3 pt-2">
                        <Button
                            type="button"
                            variant="outline"
                            @click="isDialogOpen = false"
                            class="px-4 py-2 border border-slate-200 dark:border-slate-800 text-sm font-medium rounded-lg hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-600 dark:text-slate-400 transition-colors"
                        >
                            Batal
                        </Button>
                        <Button
                            type="submit"
                            :disabled="isCreating || isUpdating"
                            class="px-4 py-2 bg-blue-600 hover:bg-blue-700 disabled:opacity-50 text-white text-sm font-semibold rounded-lg shadow-sm transition-colors"
                        >
                            {{ isCreating || isUpdating ? 'Menyimpan...' : 'Simpan' }}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    </div>
</template>
