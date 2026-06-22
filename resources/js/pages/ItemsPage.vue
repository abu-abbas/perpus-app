<script setup lang="ts">
import { Plus, Edit2, Trash2, Download, Upload, Search, FileDown, BookOpen } from '@lucide/vue';
import { toTypedSchema } from '@vee-validate/zod';
import { useForm, useField } from 'vee-validate';
import { ref, watch } from 'vue';
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
import { useItems } from '@/composables/useItems';
import { useAuthStore } from '@/stores/auth';
import type { Item } from '@/types';


const authStore = useAuthStore();
const filters = ref({
    search: '',
    type: '',
    category_id: '',
    page: 1,
    per_page: 10,
});

const {
    itemsResponse,
    items,
    isLoading,
    isError,
    createItem,
    isCreating,
    updateItem,
    isUpdating,
    deleteItem,
    importItems,
    isImporting,
    exportItems
} = useItems(filters);

const { categories } = useCategories();

const isDialogOpen = ref(false);
const isImportOpen = ref(false);
const editingItem = ref<Item | null>(null);
const coverImageFile = ref<File | null>(null);
const importFile = ref<File | null>(null);

// Skema Zod Validasi untuk Item
const itemSchema = z.object({
    type: z.enum(['book', 'magazine', 'dvd']),
    category_id: z.coerce.number().min(1, 'Kategori harus dipilih'),
    title: z.string().min(1, 'Judul wajib diisi').max(255, 'Judul maksimal 255 karakter'),
    author: z.string().min(1, 'Penulis/Sutradara wajib diisi').max(255, 'Penulis maksimal 255 karakter'),
    publisher: z.string().max(255, 'Penerbit maksimal 255 karakter').nullable().optional(),
    year: z.coerce.number().min(1000, 'Tahun minimal 1000').max(new Date().getFullYear() + 2, 'Tahun tidak valid'),
    code: z.string().min(1, 'Kode (ISBN dll) wajib diisi').max(100, 'Kode maksimal 100 karakter'),
    total_stock: z.coerce.number().min(0, 'Stok tidak boleh negatif'),
    // Attributes spesifik berdasarkan tipe
    pages: z.coerce.number().min(1, 'Jumlah halaman minimal 1').optional(),
    edition_number: z.coerce.number().min(1, 'Nomor edisi minimal 1').optional(),
    duration_minutes: z.coerce.number().min(1, 'Durasi minimal 1 menit').optional(),
});

const { handleSubmit, errors, resetForm, setValues, values: formValues } = useForm({
    validationSchema: toTypedSchema(itemSchema),
    initialValues: {
        type: 'book',
        total_stock: 1,
    }
});

const { value: type } = useField<string>('type');
const { value: category_id } = useField<number>('category_id');
const { value: title } = useField<string>('title');
const { value: author } = useField<string>('author');
const { value: publisher } = useField<string | undefined>('publisher');
const { value: year } = useField<number>('year');
const { value: code } = useField<string>('code');
const { value: total_stock } = useField<number>('total_stock');
const { value: pages } = useField<number | undefined>('pages');
const { value: edition_number } = useField<number | undefined>('edition_number');
const { value: duration_minutes } = useField<number | undefined>('duration_minutes');

function openAddDialog() {
    editingItem.value = null;
    coverImageFile.value = null;
    resetForm();
    isDialogOpen.value = true;
}

function openEditDialog(item: Item) {
    editingItem.value = item;
    coverImageFile.value = null;
    resetForm();
    setValues({
        type: item.type,
        category_id: item.category_id,
        title: item.title,
        author: item.author,
        publisher: item.publisher ?? undefined,
        year: item.year,
        code: item.code,
        total_stock: item.total_stock,
        pages: item.attributes?.pages ?? undefined,
        edition_number: item.attributes?.edition_number ?? undefined,
        duration_minutes: item.attributes?.duration_minutes ?? undefined,
    });
    isDialogOpen.value = true;
}

const onSubmit = handleSubmit(async (values: any) => {
    try {
        const formData = new FormData();
        formData.append('type', values.type);
        formData.append('category_id', String(values.category_id));
        formData.append('title', values.title);
        formData.append('author', values.author);

        if (values.publisher) {
            formData.append('publisher', values.publisher);
        }

        formData.append('year', String(values.year));
        formData.append('code', values.code);
        formData.append('total_stock', String(values.total_stock));

        // Tambah cover image file jika dipilih
        if (coverImageFile.value) {
            formData.append('cover_image', coverImageFile.value);
        }

        // Tentukan attribute JSON polimorfis sesuai tipe item
        const attrs: Record<string, any> = {};

        if (values.type === 'book' && values.pages) {
            attrs.pages = values.pages;
        } else if (values.type === 'magazine' && values.edition_number) {
            attrs.edition_number = values.edition_number;
        } else if (values.type === 'dvd' && values.duration_minutes) {
            attrs.duration_minutes = values.duration_minutes;
        }

        formData.append('attributes', JSON.stringify(attrs));

        if (editingItem.value) {
            await updateItem({ id: editingItem.value.id, formData });
        } else {
            await createItem(formData);
        }

        isDialogOpen.value = false;
    } catch (e) {
        console.error(e);
    }
});

function handleCoverChange(event: Event) {
    const target = event.target as HTMLInputElement;

    if (target.files?.length) {
        coverImageFile.value = target.files[0];
    }
}

async function handleDelete(id: number) {
    if (confirm('Apakah Anda yakin ingin menghapus item ini? Transaksi peminjaman yang aktif dengan item ini mungkin akan gagal.')) {
        try {
            await deleteItem(id);
        } catch (e) {
            console.error(e);
        }
    }
}

function handleImportFileChange(event: Event) {
    const target = event.target as HTMLInputElement;

    if (target.files?.length) {
        importFile.value = target.files[0];
    }
}

async function submitImport() {
    if (!importFile.value) {
        return;
    }

    try {
        await importItems(importFile.value);
        isImportOpen.value = false;
        importFile.value = null;
    } catch (e) {
        console.error(e);
    }
}

// Reset page ketika filter berubah
watch([() => filters.value.search, () => filters.value.type, () => filters.value.category_id], () => {
    filters.value.page = 1;
});
</script>

<template>
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex flex-col gap-1">
                <h1 class="text-2xl font-bold tracking-tight">Koleksi Item</h1>
                <p class="text-sm text-slate-500 dark:text-slate-400">
                    Kelola buku, majalah, dan DVD yang tersedia di perpustakaan.
                </p>
            </div>
            <div class="flex items-center gap-2 flex-wrap">
                <Button
                    variant="outline"
                    @click="isImportOpen = true"
                    class="flex items-center gap-2 px-3 py-2 border border-slate-200 dark:border-slate-800 text-sm font-semibold rounded-lg hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors"
                >
                    <Upload class="w-4 h-4" />
                    Import
                </Button>
                <div class="flex items-center gap-1">
                    <Button
                        variant="outline"
                        @click="exportItems('pdf')"
                        class="flex items-center gap-2 px-3 py-2 border border-slate-200 dark:border-slate-800 text-sm font-semibold rounded-lg hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors"
                    >
                        <FileDown class="w-4 h-4" />
                        PDF
                    </Button>
                    <Button
                        variant="outline"
                        @click="exportItems('excel')"
                        class="flex items-center gap-2 px-3 py-2 border border-slate-200 dark:border-slate-800 text-sm font-semibold rounded-lg hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors"
                    >
                        <Download class="w-4 h-4" />
                        Excel
                    </Button>
                </div>
                <Button
                    @click="openAddDialog"
                    class="flex items-center gap-2 px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg shadow-sm transition-all"
                >
                    <Plus class="w-4 h-4" />
                    Tambah Item
                </Button>
            </div>
        </div>

        <!-- Filters Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 p-4 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm transition-colors">
            <!-- Search bar -->
            <div class="sm:col-span-2 relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                    <Search class="w-4.5 h-4.5" />
                </div>
                <Input
                    v-model="filters.search"
                    type="text"
                    placeholder="Cari judul, penulis, atau kode..."
                    class="block w-full pl-10 pr-3 h-10 text-sm border border-slate-200 dark:border-slate-800 rounded-lg focus-visible:ring-blue-500 bg-transparent"
                />
            </div>

            <!-- Filter Type -->
            <select
                v-model="filters.type"
                class="block w-full h-10 px-3 py-2 text-sm border border-slate-200 dark:border-slate-800 rounded-lg focus:outline-none focus:ring-1 focus:ring-blue-500 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-200"
            >
                <option value="">Semua Tipe</option>
                <option value="book">Buku</option>
                <option value="magazine">Majalah</option>
                <option value="dvd">DVD</option>
            </select>

            <!-- Filter Category -->
            <select
                v-model="filters.category_id"
                class="block w-full h-10 px-3 py-2 text-sm border border-slate-200 dark:border-slate-800 rounded-lg focus:outline-none focus:ring-1 focus:ring-blue-500 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-200"
            >
                <option value="">Semua Kategori</option>
                <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
            </select>
        </div>

        <!-- Loader / Error States -->
        <div v-if="isLoading" class="p-8 text-center text-slate-500">
            Memuat data koleksi item...
        </div>
        <div v-else-if="isError" class="p-4 rounded-xl bg-red-50 dark:bg-red-950/20 text-red-600 dark:text-red-400 text-sm border border-red-200 dark:border-red-900/50">
            Gagal mengambil data koleksi.
        </div>

        <!-- Items Table -->
        <div v-else class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden transition-colors">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-sm">
                    <thead>
                        <tr class="border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/20 text-slate-500 font-semibold">
                            <th class="py-3.5 px-6">Cover</th>
                            <th class="py-3.5 px-6">Info Item</th>
                            <th class="py-3.5 px-6">Kategori / Tipe</th>
                            <th class="py-3.5 px-6">Kode / ISBN</th>
                            <th class="py-3.5 px-6">Stok (Tersedia)</th>
                            <th class="py-3.5 px-6 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800/80">
                        <tr v-for="item in items" :key="item.id" class="hover:bg-slate-50/40 dark:hover:bg-slate-800/10">
                            <!-- Cover thumbnail -->
                            <td class="py-3 px-6">
                                <div class="w-12 h-16 rounded-md bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 overflow-hidden flex items-center justify-center">
                                    <img v-if="item.cover_image_url" :src="item.cover_image_url" alt="Cover" class="w-full h-full object-cover" />
                                    <BookOpen v-else class="w-6 h-6 text-slate-400" />
                                </div>
                            </td>
                            <!-- Title, author, year & display_info -->
                            <td class="py-3 px-6">
                                <div class="flex flex-col gap-0.5">
                                    <span class="font-bold text-slate-900 dark:text-white">{{ item.title }}</span>
                                    <span class="text-xs text-slate-500 dark:text-slate-400">Oleh: {{ item.author }} ({{ item.year }})</span>
                                    <!-- Polymorphism Info Display -->
                                    <span class="text-xs px-2 py-0.5 mt-1 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 rounded-md self-start">
                                        <template v-if="item.type === 'book'">{{ item.display_info?.halaman }} Halaman</template>
                                        <template v-else-if="item.type === 'magazine'">Edisi ke-{{ item.display_info?.edisi }}</template>
                                        <template v-else-if="item.type === 'dvd'">Durasi {{ item.display_info?.durasi_menit }} menit</template>
                                    </span>
                                </div>
                            </td>
                            <!-- Category & Type -->
                            <td class="py-3 px-6">
                                <div class="flex flex-col gap-1">
                                    <span class="text-xs font-semibold px-2 py-0.5 bg-blue-50 dark:bg-blue-950/50 text-blue-600 dark:text-blue-400 rounded-md self-start border border-blue-100 dark:border-blue-900/30">
                                        {{ item.category?.name || 'Umum' }}
                                    </span>
                                    <span class="text-xs font-semibold px-2 py-0.5 bg-slate-100 dark:bg-slate-800 text-slate-650 dark:text-slate-400 rounded-md self-start capitalize">
                                        {{ item.type === 'book' ? 'Buku' : item.type === 'magazine' ? 'Majalah' : 'DVD' }}
                                    </span>
                                </div>
                            </td>
                            <!-- Code -->
                            <td class="py-3 px-6 font-mono text-xs">{{ item.code }}</td>
                            <!-- Stock status -->
                            <td class="py-3 px-6">
                                <span class="font-semibold text-slate-800 dark:text-slate-200">{{ item.total_stock }}</span>
                                <span class="text-xs text-slate-400 block">Tersedia: {{ item.available_stock }}</span>
                            </td>
                            <!-- Actions -->
                            <td class="py-3 px-6 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        @click="openEditDialog(item)"
                                        class="p-1.5 h-8 w-8 text-slate-500 hover:text-blue-600 dark:hover:text-blue-400 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-md transition-colors"
                                    >
                                        <Edit2 class="w-4 h-4" />
                                    </Button>
                                    <Button
                                        v-if="authStore.isAdmin"
                                        variant="ghost"
                                        size="icon"
                                        @click="handleDelete(item.id)"
                                        class="p-1.5 h-8 w-8 text-slate-500 hover:text-red-600 dark:hover:text-red-400 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-md transition-colors"
                                    >
                                        <Trash2 class="w-4 h-4" />
                                    </Button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="!items?.length">
                            <td colspan="6" class="py-8 text-center text-slate-400">Tidak ada item yang ditemukan.</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination footer -->
            <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-800 flex items-center justify-between">
                <span class="text-xs text-slate-500">
                    Menampilkan {{ itemsResponse?.meta?.from || 0 }} - {{ itemsResponse?.meta?.to || 0 }} dari {{ itemsResponse?.meta?.total || 0 }} item
                </span>
                <div class="flex items-center gap-2">
                    <Button
                        variant="outline"
                        size="sm"
                        :disabled="filters.page <= 1"
                        @click="filters.page--"
                        class="px-3 h-8 rounded-lg text-xs"
                    >
                        Sebelumnya
                    </Button>
                    <Button
                        variant="outline"
                        size="sm"
                        :disabled="!itemsResponse?.links?.next"
                        @click="filters.page++"
                        class="px-3 h-8 rounded-lg text-xs"
                    >
                        Selanjutnya
                    </Button>
                </div>
            </div>
        </div>

        <!-- Add/Edit Dialog Modal -->
        <Dialog v-model:open="isDialogOpen">
            <DialogContent class="sm:max-w-xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 transition-colors duration-300 max-h-[90vh] overflow-y-auto">
                <DialogHeader class="space-y-1">
                    <DialogTitle class="text-lg font-bold">
                        {{ editingItem ? 'Perbarui Item Perpustakaan' : 'Tambah Item Baru' }}
                    </DialogTitle>
                </DialogHeader>
                <form @submit.prevent="onSubmit" class="space-y-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- Tipe Item -->
                        <div class="space-y-1.5">
                            <Label class="text-xs font-semibold text-slate-550 dark:text-slate-400 uppercase tracking-wider">Tipe Item</Label>
                            <select
                                v-model="type"
                                :disabled="!!editingItem"
                                class="block w-full h-10 px-3 py-2 border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-850 rounded-lg text-sm focus:outline-none focus:ring-1 focus:ring-blue-500 text-slate-700 dark:text-slate-200"
                            >
                                <option value="book">Buku</option>
                                <option value="magazine">Majalah</option>
                                <option value="dvd">DVD</option>
                            </select>
                        </div>

                        <!-- Kategori -->
                        <div class="space-y-1.5">
                            <Label class="text-xs font-semibold text-slate-550 dark:text-slate-400 uppercase tracking-wider">Kategori</Label>
                            <select
                                v-model="category_id"
                                class="block w-full h-10 px-3 py-2 border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-850 rounded-lg text-sm focus:outline-none focus:ring-1 focus:ring-blue-500 text-slate-700 dark:text-slate-200"
                                :class="[errors.category_id ? 'border-red-500' : '']"
                            >
                                <option value="">-- Pilih Kategori --</option>
                                <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                            </select>
                            <p v-if="errors.category_id" class="text-xs text-red-500">{{ errors.category_id }}</p>
                        </div>
                    </div>

                    <!-- Judul -->
                    <div class="space-y-1.5">
                        <Label class="text-xs font-semibold text-slate-550 dark:text-slate-400 uppercase tracking-wider">Judul Item</Label>
                        <Input
                            v-model="title"
                            type="text"
                            placeholder="Contoh: Belajar Laravel SPA, Majalah Bobo, DVD Tutorial"
                            class="block w-full px-3 py-2 border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-850 rounded-lg text-sm focus-visible:ring-blue-500"
                            :class="[errors.title ? 'border-red-500 focus-visible:ring-red-500' : '']"
                        />
                        <p v-if="errors.title" class="text-xs text-red-500">{{ errors.title }}</p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- Penulis / Pembuat -->
                        <div class="space-y-1.5">
                            <Label class="text-xs font-semibold text-slate-550 dark:text-slate-400 uppercase tracking-wider">Penulis / Sutradara</Label>
                            <Input
                                v-model="author"
                                type="text"
                                placeholder="Nama penulis/pembuat..."
                                class="block w-full px-3 py-2 border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-850 rounded-lg text-sm focus-visible:ring-blue-500"
                                :class="[errors.author ? 'border-red-500 focus-visible:ring-red-500' : '']"
                            />
                            <p v-if="errors.author" class="text-xs text-red-500">{{ errors.author }}</p>
                        </div>

                        <!-- Penerbit -->
                        <div class="space-y-1.5">
                            <Label class="text-xs font-semibold text-slate-550 dark:text-slate-400 uppercase tracking-wider">Penerbit</Label>
                            <Input
                                v-model="publisher"
                                type="text"
                                placeholder="Nama penerbit (opsional)..."
                                class="block w-full px-3 py-2 border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-850 rounded-lg text-sm focus-visible:ring-blue-500"
                                :class="[errors.publisher ? 'border-red-500 focus-visible:ring-red-500' : '']"
                            />
                            <p v-if="errors.publisher" class="text-xs text-red-500">{{ errors.publisher }}</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <!-- Tahun Terbit -->
                        <div class="space-y-1.5">
                            <Label class="text-xs font-semibold text-slate-550 dark:text-slate-400 uppercase tracking-wider">Tahun Terbit</Label>
                            <Input
                                v-model="year"
                                type="number"
                                class="block w-full px-3 py-2 border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-850 rounded-lg text-sm focus-visible:ring-blue-500"
                                :class="[errors.year ? 'border-red-500 focus-visible:ring-red-500' : '']"
                            />
                            <p v-if="errors.year" class="text-xs text-red-500">{{ errors.year }}</p>
                        </div>

                        <!-- Kode Unik / ISBN -->
                        <div class="space-y-1.5">
                            <Label class="text-xs font-semibold text-slate-550 dark:text-slate-400 uppercase tracking-wider">ISBN / Kode Unik</Label>
                            <Input
                                v-model="code"
                                type="text"
                                placeholder="ISBN atau kode unik..."
                                class="block w-full px-3 py-2 border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-850 rounded-lg text-sm focus-visible:ring-blue-500"
                                :class="[errors.code ? 'border-red-500 focus-visible:ring-red-500' : '']"
                            />
                            <p v-if="errors.code" class="text-xs text-red-500">{{ errors.code }}</p>
                        </div>

                        <!-- Total Stok -->
                        <div class="space-y-1.5">
                            <Label class="text-xs font-semibold text-slate-550 dark:text-slate-400 uppercase tracking-wider">Total Stok</Label>
                            <Input
                                v-model="total_stock"
                                type="number"
                                class="block w-full px-3 py-2 border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-850 rounded-lg text-sm focus-visible:ring-blue-500"
                                :class="[errors.total_stock ? 'border-red-500 focus-visible:ring-red-500' : '']"
                            />
                            <p v-if="errors.total_stock" class="text-xs text-red-500">{{ errors.total_stock }}</p>
                        </div>
                    </div>

                    <!-- CONDITIONAL FORM FIELDS (Polimorfisme Subclass Kriteria) -->
                    <!-- Buku: Halaman -->
                    <div v-if="formValues.type === 'book'" class="space-y-1.5">
                        <Label class="text-xs font-semibold text-slate-550 dark:text-slate-400 uppercase tracking-wider">Jumlah Halaman (Spesifik Buku)</Label>
                        <Input
                            v-model="pages"
                            type="number"
                            placeholder="Contoh: 350"
                            class="block w-full px-3 py-2 border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-850 rounded-lg text-sm focus-visible:ring-blue-500"
                            :class="[errors.pages ? 'border-red-500 focus-visible:ring-red-500' : '']"
                        />
                        <p v-if="errors.pages" class="text-xs text-red-500">{{ errors.pages }}</p>
                    </div>

                    <!-- Majalah: Nomor Edisi -->
                    <div v-if="formValues.type === 'magazine'" class="space-y-1.5">
                        <Label class="text-xs font-semibold text-slate-550 dark:text-slate-400 uppercase tracking-wider">Nomor Edisi (Spesifik Majalah)</Label>
                        <Input
                            v-model="edition_number"
                            type="number"
                            placeholder="Contoh: 12"
                            class="block w-full px-3 py-2 border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-850 rounded-lg text-sm focus-visible:ring-blue-500"
                            :class="[errors.edition_number ? 'border-red-500 focus-visible:ring-red-500' : '']"
                        />
                        <p v-if="errors.edition_number" class="text-xs text-red-500">{{ errors.edition_number }}</p>
                    </div>

                    <!-- DVD: Durasi Menit -->
                    <div v-if="formValues.type === 'dvd'" class="space-y-1.5">
                        <Label class="text-xs font-semibold text-slate-550 dark:text-slate-400 uppercase tracking-wider">Durasi Menit (Spesifik DVD)</Label>
                        <Input
                            v-model="duration_minutes"
                            type="number"
                            placeholder="Contoh: 120"
                            class="block w-full px-3 py-2 border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-850 rounded-lg text-sm focus-visible:ring-blue-500"
                            :class="[errors.duration_minutes ? 'border-red-500 focus-visible:ring-red-500' : '']"
                        />
                        <p v-if="errors.duration_minutes" class="text-xs text-red-500">{{ errors.duration_minutes }}</p>
                    </div>

                    <!-- Cover Image Upload -->
                    <div class="space-y-1.5">
                        <Label class="text-xs font-semibold text-slate-550 dark:text-slate-400 uppercase tracking-wider">Cover Image (Max 2MB)</Label>
                        <input
                            type="file"
                            accept="image/*"
                            @change="handleCoverChange"
                            class="block w-full text-sm text-slate-500 dark:text-slate-400 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-blue-50 dark:file:bg-blue-950/40 file:text-blue-700 dark:file:text-blue-400 hover:file:bg-blue-100"
                        />
                    </div>

                    <!-- Dialog Footer -->
                    <DialogFooter class="flex items-center justify-end gap-3 pt-2">
                        <Button
                            type="button"
                            variant="outline"
                            @click="isDialogOpen = false"
                            class="px-4 py-2 border border-slate-200 dark:border-slate-800 text-sm font-medium rounded-lg hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-600 dark:text-slate-400"
                        >
                            Batal
                        </Button>
                        <Button
                            type="submit"
                            :disabled="isCreating || isUpdating"
                            class="px-4 py-2 bg-blue-600 hover:bg-blue-700 disabled:opacity-50 text-white text-sm font-semibold rounded-lg shadow-sm"
                        >
                            {{ isCreating || isUpdating ? 'Menyimpan...' : 'Simpan' }}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>

        <!-- Import Dialog Modal -->
        <Dialog v-model:open="isImportOpen">
            <DialogContent class="sm:max-w-md bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 transition-colors duration-300">
                <DialogHeader class="space-y-1">
                    <DialogTitle class="text-lg font-bold">Import Data Item Massal</DialogTitle>
                </DialogHeader>
                <div class="space-y-4">
                    <p class="text-xs text-slate-500 dark:text-slate-400">
                        Unggah file Excel (.xlsx) atau CSV yang berisi data item perpustakaan sesuai format yang didukung.
                    </p>
                    <input
                        type="file"
                        accept=".xlsx,.csv"
                        @change="handleImportFileChange"
                        class="block w-full text-sm text-slate-500 dark:text-slate-400 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
                    />
                    <DialogFooter class="flex items-center justify-end gap-3 pt-2">
                        <Button
                            type="button"
                            variant="outline"
                            @click="isImportOpen = false"
                            class="px-4 py-2 border border-slate-200 dark:border-slate-800 text-sm font-medium rounded-lg hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-600 dark:text-slate-400"
                        >
                            Batal
                        </Button>
                        <Button
                            @click="submitImport"
                            :disabled="isImporting || !importFile"
                            class="px-4 py-2 bg-blue-600 hover:bg-blue-700 disabled:opacity-50 text-white text-sm font-semibold rounded-lg shadow-sm"
                        >
                            {{ isImporting ? 'Mengimport...' : 'Mulai Import' }}
                        </Button>
                    </DialogFooter>
                </div>
            </DialogContent>
        </Dialog>
    </div>
</template>
