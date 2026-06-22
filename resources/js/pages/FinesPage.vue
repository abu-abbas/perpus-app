<script setup lang="ts">
import { CheckCircle } from '@lucide/vue';
import { ref, watch } from 'vue';

import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { useFines } from '@/composables/useFines';

const filters = ref({
    paid_status: '',
    page: 1,
    per_page: 10,
});

const {
    finesResponse,
    fines,
    isLoading,
    isError,
    payFine
} = useFines(filters);

function formatRupiah(value: number) {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0
    }).format(value);
}

async function handlePay(id: number) {
    if (confirm('Apakah Anda yakin ingin menandai denda ini sebagai LUNAS?')) {
        try {
            await payFine(id);
        } catch (e) {
            console.error(e);
        }
    }
}

watch(() => filters.value.paid_status, () => {
    filters.value.page = 1;
});
</script>

<template>
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex items-center justify-between">
            <div class="flex flex-col gap-1">
                <h1 class="text-2xl font-bold tracking-tight">Daftar Denda</h1>
                <p class="text-sm text-slate-500 dark:text-slate-400">
                    Kelola tagihan denda keterlambatan atau kerusakan/kehilangan dari peminjam.
                </p>
            </div>
        </div>

        <!-- Filters Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 p-4 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm transition-colors">
            <!-- Filter Status Bayar -->
            <select
                v-model="filters.paid_status"
                class="block w-full h-10 px-3 py-2 text-sm border border-slate-200 dark:border-slate-800 rounded-lg focus:outline-none focus:ring-1 focus:ring-blue-500 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-200"
            >
                <option value="">Semua Status Denda</option>
                <option value="unpaid">Belum Dibayar</option>
                <option value="paid">Lunas</option>
            </select>
        </div>

        <!-- Loader / Error States -->
        <div v-if="isLoading" class="p-8 text-center text-slate-500">
            Memuat data denda...
        </div>
        <div v-else-if="isError" class="p-4 rounded-xl bg-red-50 dark:bg-red-950/20 text-red-600 dark:text-red-400 text-sm border border-red-200 dark:border-red-900/50">
            Gagal mengambil data denda.
        </div>

        <!-- Fines Table -->
        <div v-else class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden transition-colors">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-sm">
                    <thead>
                        <tr class="border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/20 text-slate-500 font-semibold">
                            <th class="py-3.5 px-6">Anggota</th>
                            <th class="py-3.5 px-6">Item Dipinjam</th>
                            <th class="py-3.5 px-6">Jumlah Denda</th>
                            <th class="py-3.5 px-6">Alasan</th>
                            <th class="py-3.5 px-6">Tgl Lunas</th>
                            <th class="py-3.5 px-6">Status</th>
                            <th class="py-3.5 px-6 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800/80">
                        <tr v-for="fine in fines" :key="fine.id" class="hover:bg-slate-50/40 dark:hover:bg-slate-800/10">
                            <!-- Member info -->
                            <td class="py-4 px-6">
                                <div class="flex flex-col">
                                    <span class="font-semibold text-slate-900 dark:text-white">{{ fine.loan?.member?.full_name }}</span>
                                    <span class="text-xs text-slate-400 font-mono">{{ fine.loan?.member?.member_number }}</span>
                                </div>
                            </td>
                            <!-- Item info -->
                            <td class="py-4 px-6">
                                <span class="font-medium">{{ fine.loan?.item?.title }}</span>
                            </td>
                            <!-- Amount -->
                            <td class="py-4 px-6 font-bold text-slate-900 dark:text-white">
                                {{ formatRupiah(fine.amount) }}
                            </td>
                            <!-- Reason -->
                            <td class="py-4 px-6 capitalize">
                                <Badge
                                    variant="secondary"
                                    class="px-2 py-0.5 text-xs rounded-md font-semibold"
                                    :class="{
                                        'text-orange-600 dark:text-orange-400 bg-orange-50/55 dark:bg-orange-950/20 border border-orange-100 dark:border-orange-900/30': fine.reason === 'late',
                                        'text-yellow-600 dark:text-yellow-500 bg-yellow-50/55 dark:bg-yellow-950/20 border border-yellow-100 dark:border-yellow-900/30': fine.reason === 'damaged',
                                        'text-red-600 dark:text-red-400 bg-red-50/55 dark:bg-red-950/20 border border-red-100 dark:border-red-900/30': fine.reason === 'lost',
                                    }">
                                    {{
                                        fine.reason === 'late' ? 'Keterlambatan' :
                                        fine.reason === 'damaged' ? 'Rusak' : 'Hilang'
                                    }}
                                </Badge>
                            </td>
                            <!-- Paid Date -->
                            <td class="py-4 px-6 text-slate-500 dark:text-slate-400">
                                {{ fine.paid_date || '-' }}
                            </td>
                            <!-- Status -->
                            <td class="py-4 px-6">
                                <Badge
                                    variant="secondary"
                                    class="px-2.5 py-0.5 text-xs font-semibold rounded-full inline-block"
                                    :class="{
                                        'bg-green-50 dark:bg-green-950/40 text-green-600 dark:text-green-400 border border-green-100 dark:border-green-900/30': fine.paid_status === 'paid',
                                        'bg-red-50 dark:bg-red-950/40 text-red-600 dark:text-red-400 border border-red-100 dark:border-red-900/30': fine.paid_status === 'unpaid',
                                    }">
                                    {{ fine.paid_status === 'paid' ? 'Lunas' : 'Belum Lunas' }}
                                </Badge>
                            </td>
                            <!-- Settle Action -->
                            <td class="py-4 px-6 text-right">
                                <Button
                                    v-if="fine.paid_status === 'unpaid'"
                                    @click="handlePay(fine.id)"
                                    variant="default"
                                    size="sm"
                                    class="flex items-center gap-1.5 px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold rounded-lg shadow-sm transition-colors ml-auto h-8"
                                >
                                    <CheckCircle class="w-3.5 h-3.5" />
                                    Tandai Lunas
                                </Button>
                                <span v-else class="text-slate-400 text-xs">-</span>
                            </td>
                        </tr>
                        <tr v-if="!fines?.length">
                            <td colspan="7" class="py-8 text-center text-slate-400">Tidak ada denda.</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination footer -->
            <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-800 flex items-center justify-between">
                <span class="text-xs text-slate-500">
                    Menampilkan {{ finesResponse?.meta?.from || 0 }} - {{ finesResponse?.meta?.to || 0 }} dari {{ finesResponse?.meta?.total || 0 }} denda
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
                        :disabled="!finesResponse?.links?.next"
                        @click="filters.page++"
                        class="px-3 h-8 rounded-lg text-xs"
                    >
                        Selanjutnya
                    </Button>
                </div>
            </div>
        </div>
    </div>
</template>
