<script setup lang="ts">
import { Plus, ArrowDownLeft, Search } from '@lucide/vue';
import { toTypedSchema } from '@vee-validate/zod';
import { useForm, useField } from 'vee-validate';
import { ref, watch, computed } from 'vue';
import { z } from 'zod';

import { Badge } from '@/components/ui/badge';
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
import { useItems } from '@/composables/useItems';
import { useLoans } from '@/composables/useLoans';
import { useMembers } from '@/composables/useMembers';


const filters = ref({
    search: '',
    status: '',
    page: 1,
    per_page: 10,
});



// Ambal data untuk opsi form peminjaman
const { members } = useMembers(ref({ per_page: 100 }));
const { items } = useItems(ref({ per_page: 100 }));
const {
    loans,
    loansResponse,
    isLoading,
    isError,
    createLoan,
    isCreating,
    returnLoan,
} = useLoans(filters);

// Filter agar hanya menampilkan anggota aktif
const activeMembers = computed(() => {
    return members.value.filter((m: any) => m.status === 'active');
});

// Filter agar hanya menampilkan item yang tersedia stoknya
const availableItems = computed(() => {
    return items.value.filter((i: any) => i.available_stock > 0);
});

const isDialogOpen = ref(false);

// Skema Validasi Zod untuk Peminjaman Baru
const loanSchema = z.object({
    member_id: z.coerce.number().min(1, 'Anggota wajib dipilih'),
    item_id: z.coerce.number().min(1, 'Item wajib dipilih'),
});

const { handleSubmit, errors, resetForm } = useForm({
    validationSchema: toTypedSchema(loanSchema),
});

const { value: member_id } = useField<number>('member_id');
const { value: item_id } = useField<number>('item_id');

function openAddDialog() {
    resetForm();
    member_id.value = 0;
    item_id.value = 0;
    isDialogOpen.value = true;
}

const onSubmit = handleSubmit(async (values: any) => {
    try {
        await createLoan({
            member_id: values.member_id,
            item_id: values.item_id,
        });

        isDialogOpen.value = false;
    } catch (e) {
        console.error(e);
    }
});

async function handleReturn(id: number) {
    if (confirm('Apakah Anda yakin ingin memproses pengembalian untuk item ini?')) {
        try {
            await returnLoan(id);
        } catch (e) {
            console.error(e);
        }
    }
}

watch([() => filters.value.search, () => filters.value.status], () => {
    filters.value.page = 1;
});
</script>

<template>
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div class="flex flex-col gap-1">
                <h1 class="text-2xl font-bold tracking-tight">Transaksi Peminjaman</h1>
                <p class="text-sm text-slate-500 dark:text-slate-400">
                    Proses peminjaman baru dan pengembalian koleksi perpustakaan.
                </p>
            </div>
            <Button
                @click="openAddDialog"
                class="flex items-center gap-2 px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg shadow-sm transition-all"
            >
                <Plus class="w-4 h-4" />
                Peminjaman Baru
            </Button>
        </div>

        <!-- Filters Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 p-4 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm transition-colors">
            <!-- Search bar -->
            <div class="sm:col-span-2 relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                    <Search class="w-4.5 h-4.5" />
                </div>
                <Input
                    v-model="filters.search"
                    type="text"
                    placeholder="Cari nama anggota atau judul item..."
                    class="block w-full pl-10 pr-3 h-10 text-sm border border-slate-200 dark:border-slate-800 rounded-lg focus-visible:ring-blue-500 bg-transparent"
                />
            </div>

            <!-- Filter Status -->
            <select
                v-model="filters.status"
                class="block w-full h-10 px-3 py-2 text-sm border border-slate-200 dark:border-slate-800 rounded-lg focus:outline-none focus:ring-1 focus:ring-blue-500 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-200"
            >
                <option value="">Semua Status</option>
                <option value="borrowed">Sedang Dipinjam</option>
                <option value="returned">Sudah Kembali</option>
                <option value="overdue">Terlambat</option>
                <option value="lost">Hilang</option>
            </select>
        </div>

        <!-- Loader / Error States -->
        <div v-if="isLoading" class="p-8 text-center text-slate-500">
            Memuat data transaksi peminjaman...
        </div>
        <div v-else-if="isError" class="p-4 rounded-xl bg-red-50 dark:bg-red-950/20 text-red-600 dark:text-red-400 text-sm border border-red-200 dark:border-red-900/50">
            Gagal mengambil data transaksi peminjaman.
        </div>

        <!-- Loans Table -->
        <div v-else class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden transition-colors">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-sm">
                    <thead>
                        <tr class="border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/20 text-slate-500 font-semibold">
                            <th class="py-3.5 px-6">Anggota</th>
                            <th class="py-3.5 px-6">Item</th>
                            <th class="py-3.5 px-6">Tgl Pinjam</th>
                            <th class="py-3.5 px-6">Jatuh Tempo</th>
                            <th class="py-3.5 px-6">Tgl Kembali</th>
                            <th class="py-3.5 px-6">Denda</th>
                            <th class="py-3.5 px-6">Status</th>
                            <th class="py-3.5 px-6 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800/80">
                        <tr v-for="loan in loans" :key="loan.id" class="hover:bg-slate-50/40 dark:hover:bg-slate-800/10">
                            <!-- Member Name & Number -->
                            <td class="py-4 px-6">
                                <div class="flex flex-col">
                                    <span class="font-semibold text-slate-900 dark:text-white">{{ loan.member?.full_name }}</span>
                                    <span class="text-xs text-slate-400 font-mono">{{ loan.member?.member_number }}</span>
                                </div>
                            </td>
                            <!-- Item Title & Type -->
                            <td class="py-4 px-6">
                                <div class="flex flex-col">
                                    <span class="font-semibold">{{ loan.item?.title }}</span>
                                    <span class="text-xs text-slate-500 capitalize">{{ loan.item?.type }}</span>
                                </div>
                            </td>
                            <!-- Dates -->
                            <td class="py-4 px-6 text-slate-500 dark:text-slate-400">{{ loan.loan_date }}</td>
                            <td class="py-4 px-6 text-slate-500 dark:text-slate-400">{{ loan.due_date }}</td>
                            <td class="py-4 px-6 text-slate-500 dark:text-slate-400">
                                {{ loan.return_date || '-' }}
                            </td>
                            <!-- Fines if any -->
                            <td class="py-4 px-6">
                                <div v-if="loan.fine" class="flex flex-col">
                                    <span class="font-bold text-red-650 dark:text-red-400">Rp{{ loan.fine.amount.toLocaleString('id-ID') }}</span>
                                    <span class="text-[10px] text-slate-400 capitalize">{{ loan.fine.paid_status === 'paid' ? 'Lunas' : 'Belum Bayar' }}</span>
                                </div>
                                <span v-else class="text-slate-400">-</span>
                            </td>
                            <!-- Status -->
                            <td class="py-4 px-6">
                                <Badge
                                    variant="secondary"
                                    class="px-2.5 py-0.5 text-xs font-semibold rounded-full inline-block"
                                    :class="{
                                        'bg-blue-50 dark:bg-blue-950/40 text-blue-600 dark:text-blue-400 border border-blue-100 dark:border-blue-900/30': loan.status === 'borrowed',
                                        'bg-green-50 dark:bg-green-950/40 text-green-600 dark:text-green-400 border border-green-100 dark:border-green-900/30': loan.status === 'returned',
                                        'bg-red-50 dark:bg-red-950/40 text-red-600 dark:text-red-400 border border-red-100 dark:border-red-900/30': loan.status === 'overdue',
                                        'bg-slate-100 dark:bg-slate-800 text-slate-650 dark:text-slate-400': loan.status === 'lost',
                                    }">
                                    {{
                                        loan.status === 'borrowed' ? 'Dipinjam' :
                                        loan.status === 'returned' ? 'Dikembalikan' :
                                        loan.status === 'overdue' ? 'Terlambat' : 'Hilang'
                                    }}
                                </Badge>
                            </td>
                            <!-- Return Action -->
                            <td class="py-4 px-6 text-right">
                                <Button
                                    v-if="loan.status === 'borrowed' || loan.status === 'overdue'"
                                    @click="handleReturn(loan.id)"
                                    variant="default"
                                    size="sm"
                                    class="flex items-center gap-1.5 px-3 py-1.5 bg-green-500 hover:bg-green-600 text-white text-xs font-semibold rounded-lg shadow-sm transition-colors ml-auto h-8"
                                >
                                    <ArrowDownLeft class="w-3.5 h-3.5" />
                                    Kembalikan
                                </Button>
                                <span v-else class="text-slate-400 text-xs">-</span>
                            </td>
                        </tr>
                        <tr v-if="!loans?.length">
                            <td colspan="8" class="py-8 text-center text-slate-400">Tidak ada data peminjaman.</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination footer -->
            <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-800 flex items-center justify-between">
                <span class="text-xs text-slate-500">
                    Menampilkan {{ loansResponse?.meta?.from || 0 }} - {{ loansResponse?.meta?.to || 0 }} dari {{ loansResponse?.meta?.total || 0 }} peminjaman
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
                        :disabled="!loansResponse?.links?.next"
                        @click="filters.page++"
                        class="px-3 h-8 rounded-lg text-xs"
                    >
                        Selanjutnya
                    </Button>
                </div>
            </div>
        </div>

        <!-- Add Loan Dialog Modal -->
        <Dialog v-model:open="isDialogOpen">
            <DialogContent class="sm:max-w-md bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 transition-colors duration-300">
                <DialogHeader class="space-y-1">
                    <DialogTitle class="text-lg font-bold">Proses Peminjaman Baru</DialogTitle>
                </DialogHeader>
                <form @submit.prevent="onSubmit" class="space-y-4">
                    <!-- Anggota Peminjam -->
                    <div class="space-y-1.5">
                        <Label class="text-xs font-semibold text-slate-550 dark:text-slate-400 uppercase tracking-wider">Anggota Peminjam</Label>
                        <select
                            v-model="member_id"
                            class="block w-full h-10 px-3 py-2 border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-850 rounded-lg text-sm text-slate-700 dark:text-slate-200 focus:outline-none focus:ring-1 focus:ring-blue-500"
                            :class="[errors.member_id ? 'border-red-500' : '']"
                        >
                            <option value="0">-- Pilih Anggota Aktif --</option>
                            <option v-for="m in activeMembers" :key="m.id" :value="m.id">
                                {{ m.full_name }} ({{ m.member_number }})
                            </option>
                        </select>
                        <p v-if="errors.member_id" class="text-xs text-red-500">{{ errors.member_id }}</p>
                    </div>

                    <!-- Item Koleksi -->
                    <div class="space-y-1.5">
                        <Label class="text-xs font-semibold text-slate-550 dark:text-slate-400 uppercase tracking-wider">Pilih Item</Label>
                        <select
                            v-model="item_id"
                            class="block w-full h-10 px-3 py-2 border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-850 rounded-lg text-sm text-slate-700 dark:text-slate-200 focus:outline-none focus:ring-1 focus:ring-blue-500"
                            :class="[errors.item_id ? 'border-red-500' : '']"
                        >
                            <option value="0">-- Pilih Item Tersedia --</option>
                            <option v-for="i in availableItems" :key="i.id" :value="i.id">
                                {{ i.title }} [{{ i.type.toUpperCase() }}] - Stok: {{ i.available_stock }}
                            </option>
                        </select>
                        <p v-if="errors.item_id" class="text-xs text-red-500">{{ errors.item_id }}</p>
                    </div>

                    <!-- Dialog Buttons -->
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
                            :disabled="isCreating"
                            class="px-4 py-2 bg-blue-600 hover:bg-blue-700 disabled:opacity-50 text-white text-sm font-semibold rounded-lg shadow-sm"
                        >
                            {{ isCreating ? 'Memproses...' : 'Proses Pinjam' }}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    </div>
</template>
