<script setup lang="ts">
import { FileDown, Download, Info } from '@lucide/vue';
import { ref } from 'vue';

import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardHeader, CardTitle, CardDescription, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { useReport } from '@/composables/useReport';

const filters = ref({
    date_from: '',
    date_to: '',
});

const {
    reportData,
    isLoading,
    isError,
    exportReport
} = useReport(filters);

function formatRupiah(value: number) {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0
    }).format(value);
}
</script>

<template>
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex flex-col gap-1">
                <h1 class="text-2xl font-bold tracking-tight">Laporan Transaksi</h1>
                <p class="text-sm text-slate-500 dark:text-slate-400">
                    Analisis data peminjaman, keterlambatan, dan denda dengan visualisasi polimorfisme.
                </p>
            </div>
            <div class="flex items-center gap-2">
                <Button
                    @click="exportReport('pdf')"
                    variant="outline"
                    class="flex items-center gap-2 h-9 text-xs"
                >
                    <FileDown class="w-4 h-4" />
                    Unduh PDF
                </Button>
                <Button
                    @click="exportReport('excel')"
                    variant="outline"
                    class="flex items-center gap-2 h-9 text-xs"
                >
                    <Download class="w-4 h-4" />
                    Unduh Excel
                </Button>
            </div>
        </div>

        <!-- Date Filters -->
        <Card>
            <CardContent class="p-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="space-y-2">
                    <Label for="date_from" class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-450">Mulai Tanggal</Label>
                    <Input
                        id="date_from"
                        v-model="filters.date_from"
                        type="date"
                        class="h-10 bg-transparent"
                    />
                </div>
                <div class="space-y-2">
                    <Label for="date_to" class="text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-450">Sampai Tanggal</Label>
                    <Input
                        id="date_to"
                        v-model="filters.date_to"
                        type="date"
                        class="h-10 bg-transparent"
                    />
                </div>
            </CardContent>
        </Card>

        <!-- Loader / Error States -->
        <div v-if="isLoading" class="p-8 text-center text-slate-500">
            Mengagregasi laporan transaksi...
        </div>
        <div v-else-if="isError" class="p-4 rounded-xl bg-red-50 dark:bg-red-950/20 text-red-600 dark:text-red-400 text-sm border border-red-200 dark:border-red-900/50">
            Gagal memuat laporan transaksi.
        </div>

        <!-- Report Data Content -->
        <div v-else class="space-y-8">
            <!-- Stats Summary cards -->
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-3">
                <!-- Total Loans -->
                <Card>
                    <CardHeader class="pb-2">
                        <CardDescription class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Total Peminjaman</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <h3 class="text-3xl font-extrabold text-blue-600 dark:text-blue-400">
                            {{ reportData?.total_peminjaman }}
                        </h3>
                    </CardContent>
                </Card>

                <!-- Overdue Loans -->
                <Card>
                    <CardHeader class="pb-2">
                        <CardDescription class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Peminjaman Terlambat</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <h3 class="text-3xl font-extrabold" :class="reportData?.peminjaman_terlambat > 0 ? 'text-amber-600 dark:text-amber-400' : 'text-slate-700 dark:text-slate-300'">
                            {{ reportData?.peminjaman_terlambat }}
                        </h3>
                    </CardContent>
                </Card>

                <!-- Total Fines -->
                <Card>
                    <CardHeader class="pb-2">
                        <CardDescription class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Total Denda Akumulasi</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <h3 class="text-3xl font-extrabold text-green-600 dark:text-green-400">
                            {{ formatRupiah(reportData?.total_denda ?? 0) }}
                        </h3>
                    </CardContent>
                </Card>
            </div>

            <!-- Polymorphism Visualization Section (Ujian Poin h) -->
            <div class="bg-blue-50/40 dark:bg-blue-950/20 border border-blue-100 dark:border-blue-900/60 p-6 rounded-2xl space-y-4">
                <div class="flex items-start gap-3">
                    <Info class="w-5 h-5 text-blue-600 dark:text-blue-400 mt-0.5" />
                    <div class="space-y-1">
                        <h3 class="font-bold text-slate-900 dark:text-white">Demonstrasi Polimorfisme Nyata (Poin h)</h3>
                        <p class="text-xs text-slate-550 dark:text-slate-400">
                            Di bawah ini adalah pemanggilan method <code class="text-blue-600 dark:text-blue-400 font-mono">displayInfo()</code> dan <code class="text-blue-600 dark:text-blue-400 font-mono">calculateLateFee()</code> secara dinamis pada runtime melalui referensi tipe data dasar <code class="font-mono text-slate-800 dark:text-slate-200">Item</code>, menghasilkan display info spesifik untuk subclass Book, Magazine, dan Dvd.
                        </p>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <Card v-for="info in reportData?.item_info_polimorfisme?.slice(0, 6)" :key="info.id" class="border border-slate-200 dark:border-slate-800/80">
                        <CardHeader class="p-4 pb-2 flex flex-row items-center justify-between space-y-0">
                            <span class="text-[10px] font-mono font-bold text-slate-400">ITEM ID: #{{ info.id }}</span>
                            <Badge variant="secondary" class="text-[9px] font-bold px-2 py-0.5 bg-blue-50 dark:bg-blue-950/50 text-blue-600 dark:text-blue-400 uppercase rounded-full">Polimorfis</Badge>
                        </CardHeader>
                        <CardContent class="p-4 pt-0 space-y-2">
                            <div class="text-xs font-semibold text-slate-800 dark:text-slate-200">
                                {{ info.display_info?.judul }}
                            </div>
                            <div class="text-[11px] text-slate-550 dark:text-slate-400 font-medium">
                                <span v-if="info.display_info?.tipe === 'Buku'">Buku • {{ info.display_info?.halaman }} Halaman</span>
                                <span v-else-if="info.display_info?.tipe === 'Majalah'">Majalah • Edisi ke-{{ info.display_info?.edisi }}</span>
                                <span v-else-if="info.display_info?.tipe === 'DVD'">DVD • Durasi {{ info.display_info?.durasi_menit }} menit</span>
                            </div>
                            <p class="text-[11px] text-slate-500 dark:text-slate-450">Tarif Denda: <span class="font-bold text-red-650 dark:text-red-400">{{ formatRupiah(info.denda_per_hari) }}</span> / hari</p>
                        </CardContent>
                    </Card>
                </div>
            </div>

            <!-- Details Report Table -->
            <Card class="overflow-hidden">
                <CardHeader class="p-6 border-b border-slate-200 dark:border-slate-800">
                    <CardTitle class="text-base font-bold">Detail Laporan Peminjaman</CardTitle>
                </CardHeader>
                <CardContent class="p-0">
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
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-800/80">
                                <tr v-for="row in reportData?.detail_peminjaman" :key="row.id" class="hover:bg-slate-50/40 dark:hover:bg-slate-800/10">
                                    <td class="py-4 px-6">
                                        <div class="flex flex-col">
                                            <span class="font-semibold">{{ row.member_name }}</span>
                                            <span class="text-xs text-slate-400 font-mono">{{ row.member_number }}</span>
                                        </div>
                                    </td>
                                    <td class="py-4 px-6">
                                        <div class="flex flex-col">
                                            <span class="font-semibold">{{ row.item_title }}</span>
                                            <span class="text-xs text-slate-500 capitalize">{{ row.item_type }}</span>
                                        </div>
                                    </td>
                                    <td class="py-4 px-6 text-slate-500 dark:text-slate-400">{{ row.loan_date }}</td>
                                    <td class="py-4 px-6 text-slate-500 dark:text-slate-400">{{ row.due_date }}</td>
                                    <td class="py-4 px-6 text-slate-500 dark:text-slate-400">{{ row.return_date || '-' }}</td>
                                    <td class="py-4 px-6 font-bold" :class="row.fine_amount > 0 ? 'text-red-600 dark:text-red-400' : 'text-slate-400'">
                                        {{ row.fine_amount > 0 ? formatRupiah(row.fine_amount) : '-' }}
                                    </td>
                                    <td class="py-4 px-6">
                                        <Badge
                                            variant="secondary"
                                            class="px-2.5 py-0.5 text-xs font-semibold rounded-full inline-block"
                                            :class="{
                                                'bg-blue-50 dark:bg-blue-950/40 text-blue-600 dark:text-blue-400 border border-blue-100 dark:border-blue-900/30': row.status === 'borrowed',
                                                'bg-green-50 dark:bg-green-950/40 text-green-600 dark:text-green-400 border border-green-100 dark:border-green-900/30': row.status === 'returned',
                                                'bg-red-50 dark:bg-red-950/40 text-red-600 dark:text-red-400 border border-red-100 dark:border-red-900/30': row.status === 'overdue',
                                                'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 border border-slate-200 dark:border-slate-700': row.status === 'lost',
                                            }">
                                            {{
                                                row.status === 'borrowed' ? 'Dipinjam' :
                                                row.status === 'returned' ? 'Kembali' :
                                                row.status === 'overdue' ? 'Terlambat' : 'Hilang'
                                            }}
                                        </Badge>
                                    </td>
                                </tr>
                                <tr v-if="!reportData?.detail_peminjaman?.length">
                                    <td colspan="7" class="py-8 text-center text-slate-400">Tidak ada data untuk rentang tanggal tersebut.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </CardContent>
            </Card>
        </div>
    </div>
</template>
