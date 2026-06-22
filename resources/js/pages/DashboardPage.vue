<script setup lang="ts">
import {
    BookOpen,
    Users,
    ClipboardList,
    AlertTriangle,
    Receipt,
    PlusCircle,
    ArrowDownLeft,
    UserPlus
} from '@lucide/vue';
import { useRouter } from 'vue-router';

import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardHeader, CardTitle, CardDescription, CardContent } from '@/components/ui/card';
import { useDashboard } from '@/composables/useDashboard';


const { summary, isLoading, isError } = useDashboard();
const router = useRouter();

function formatRupiah(value: number) {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0
    }).format(value);
}
</script>

<template>
    <div class="space-y-8 pb-8 animate-fade-in duration-500">
        <!-- Top Title & Description with visual accents -->
        <div class="flex flex-col gap-1.5 relative pl-4 border-l-4 border-blue-600 dark:border-blue-500 py-1">
            <h1 class="text-3xl font-extrabold tracking-tight text-slate-900 dark:text-white sm:text-4xl bg-linear-to-r from-slate-900 to-slate-700 dark:from-white dark:to-slate-300 bg-clip-text text-transparent">
                Ringkasan Dashboard
            </h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 font-medium">
                Statistik operasional perpustakaan yang diupdate secara real-time.
            </p>
        </div>

        <!-- Loading State (Shimmer skeleton premium) -->
        <div v-if="isLoading" class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
            <Card v-for="i in 4" :key="i" class="h-36 bg-white dark:bg-slate-900 border border-slate-200/60 dark:border-slate-800/80 rounded-2xl animate-pulse relative overflow-hidden">
                <div class="absolute inset-0 bg-linear-to-r from-transparent via-slate-100/30 dark:via-slate-800/20 to-transparent translate-x-[-100%] animate-shimmer"></div>
            </Card>
        </div>

        <!-- Error State -->
        <div v-else-if="isError" class="p-6 rounded-2xl bg-red-50/60 dark:bg-red-950/10 text-red-600 dark:text-red-400 text-sm border border-red-200/50 dark:border-red-900/30 backdrop-blur-md">
            <div class="flex items-center gap-3">
                <AlertTriangle class="w-5 h-5 text-red-500 animate-bounce" />
                <span class="font-semibold">Gagal mengambil data dashboard. Silakan refresh halaman.</span>
            </div>
        </div>

        <!-- Dashboard Content -->
        <div v-else class="space-y-8">
            <!-- Stats Cards Grid (dashboard-01 style with premium hover cards) -->
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
                <!-- Card 1: Denda Aktif -->
                <Card class="group bg-linear-to-br from-white to-slate-50/50 dark:from-slate-900 dark:to-slate-950 border border-slate-200/60 dark:border-slate-800/80 shadow-xs hover:shadow-md rounded-2xl transition-all duration-300 hover:-translate-y-1 hover:border-blue-500/30 overflow-hidden relative">
                    <div class="absolute inset-x-0 top-0 h-[2px] bg-linear-to-r from-blue-500 to-indigo-500 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-3">
                        <CardTitle class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Denda Aktif</CardTitle>
                        <div class="p-2 bg-blue-50 dark:bg-blue-950/60 rounded-xl text-blue-600 dark:text-blue-400 group-hover:scale-110 transition-transform">
                            <Receipt class="h-4.5 w-4.5" />
                        </div>
                    </CardHeader>
                    <CardContent class="space-y-1">
                        <div class="text-3xl font-black text-slate-900 dark:text-white tracking-tight">
                            {{ formatRupiah(summary?.total_fines_unpaid ?? 0) }}
                        </div>
                        <p class="text-[11px] text-slate-400 dark:text-slate-500 font-medium">Akumulasi denda belum lunas</p>
                    </CardContent>
                </Card>

                <!-- Card 2: Total Anggota -->
                <Card class="group bg-linear-to-br from-white to-slate-50/50 dark:from-slate-900 dark:to-slate-950 border border-slate-200/60 dark:border-slate-800/80 shadow-xs hover:shadow-md rounded-2xl transition-all duration-300 hover:-translate-y-1 hover:border-indigo-500/30 overflow-hidden relative">
                    <div class="absolute inset-x-0 top-0 h-[2px] bg-linear-to-r from-indigo-500 to-purple-500 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-3">
                        <CardTitle class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Total Anggota</CardTitle>
                        <div class="p-2 bg-indigo-50 dark:bg-indigo-950/60 rounded-xl text-indigo-600 dark:text-indigo-400 group-hover:scale-110 transition-transform">
                            <Users class="h-4.5 w-4.5" />
                        </div>
                    </CardHeader>
                    <CardContent class="space-y-1">
                        <div class="text-3xl font-black text-slate-900 dark:text-white tracking-tight">
                            {{ summary?.total_members }}
                        </div>
                        <p class="text-[11px] text-slate-400 dark:text-slate-500 font-medium">Anggota aktif terdaftar</p>
                    </CardContent>
                </Card>

                <!-- Card 3: Total Item -->
                <Card class="group bg-linear-to-br from-white to-slate-50/50 dark:from-slate-900 dark:to-slate-950 border border-slate-200/60 dark:border-slate-800/80 shadow-xs hover:shadow-md rounded-2xl transition-all duration-300 hover:-translate-y-1 hover:border-emerald-500/30 overflow-hidden relative">
                    <div class="absolute inset-x-0 top-0 h-[2px] bg-linear-to-r from-emerald-500 to-teal-500 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-3">
                        <CardTitle class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Total Item</CardTitle>
                        <div class="p-2 bg-emerald-50 dark:bg-emerald-950/60 rounded-xl text-emerald-600 dark:text-emerald-400 group-hover:scale-110 transition-transform">
                            <BookOpen class="h-4.5 w-4.5" />
                        </div>
                    </CardHeader>
                    <CardContent class="space-y-1">
                        <div class="text-3xl font-black text-slate-900 dark:text-white tracking-tight">
                            {{ summary?.total_items }}
                        </div>
                        <p class="text-[11px] text-slate-400 dark:text-slate-500 font-medium">Koleksi buku, majalah, & DVD</p>
                    </CardContent>
                </Card>

                <!-- Card 4: Pinjaman Aktif -->
                <Card class="group bg-linear-to-br from-white to-slate-50/50 dark:from-slate-900 dark:to-slate-950 border border-slate-200/60 dark:border-slate-800/80 shadow-xs hover:shadow-md rounded-2xl transition-all duration-300 hover:-translate-y-1 hover:border-rose-500/30 overflow-hidden relative">
                    <div class="absolute inset-x-0 top-0 h-[2px] bg-linear-to-r from-rose-500 to-pink-500 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-3">
                        <CardTitle class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">Pinjaman Aktif</CardTitle>
                        <div class="p-2 bg-rose-50 dark:bg-rose-950/60 rounded-xl text-rose-600 dark:text-rose-400 group-hover:scale-110 transition-transform">
                            <ClipboardList class="h-4.5 w-4.5" />
                        </div>
                    </CardHeader>
                    <CardContent class="space-y-1">
                        <div class="text-3xl font-black text-slate-900 dark:text-white tracking-tight">
                            {{ summary?.active_loans }}
                        </div>
                        <p class="text-[11px] text-slate-400 dark:text-slate-500 font-medium">Sedang dipinjam saat ini</p>
                    </CardContent>
                </Card>
            </div>

            <!-- Bottom Content Grid (dashboard-01 style with asymmetric structure) -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Left: Recent Loans (col-span-2) -->
                <Card class="lg:col-span-2 bg-white dark:bg-slate-900 border border-slate-200/65 dark:border-slate-800/80 rounded-2xl shadow-sm overflow-hidden transition-all duration-300">
                    <CardHeader class="flex flex-row items-center justify-between border-b border-slate-100 dark:border-slate-800/80 px-6 py-4.5 bg-slate-50/20 dark:bg-slate-900/10">
                        <div class="space-y-1">
                            <CardTitle class="text-lg font-bold text-slate-900 dark:text-white">Peminjaman Terbaru</CardTitle>
                            <CardDescription class="text-xs text-slate-500">Daftar transaksi peminjaman buku terakhir.</CardDescription>
                        </div>
                        <Button
                            @click="router.push('/loans')"
                            variant="outline"
                            size="sm"
                            class="h-8.5 text-xs font-semibold px-4 border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-850 hover:text-blue-600 dark:hover:text-blue-400 transition-all rounded-lg"
                        >
                            Semua Transaksi
                        </Button>
                    </CardHeader>
                    <CardContent class="p-0">
                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse text-sm">
                                <thead>
                                    <tr class="border-b border-slate-100 dark:border-slate-855 bg-slate-50/40 dark:bg-slate-800/5 text-slate-500 font-bold text-[11px] uppercase tracking-wider">
                                        <th class="py-4 px-6">Anggota</th>
                                        <th class="py-4 px-6">Item</th>
                                        <th class="py-4 px-6 text-center">Tgl Pinjam</th>
                                        <th class="py-4 px-6 text-center">Jatuh Tempo</th>
                                        <th class="py-4 px-6 text-right">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 dark:divide-slate-850">
                                    <tr v-for="loan in summary?.recent_loans" :key="loan.id" class="hover:bg-blue-50/10 dark:hover:bg-slate-850/30 transition-colors group">
                                        <!-- Member info -->
                                        <td class="py-4 px-6 font-bold text-slate-900 dark:text-white text-[13.5px]">
                                            {{ loan.member?.full_name }}
                                        </td>
                                        <!-- Item info -->
                                        <td class="py-4 px-6">
                                            <div class="flex flex-col gap-0.5">
                                                <span class="font-semibold text-slate-800 dark:text-slate-250 line-clamp-1 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">{{ loan.item?.title }}</span>
                                                <span class="text-[10px] text-slate-400 font-semibold uppercase tracking-wider">{{ loan.item?.type === 'book' ? 'Buku' : loan.item?.type === 'magazine' ? 'Majalah' : 'DVD' }}</span>
                                            </div>
                                        </td>
                                        <!-- Dates -->
                                        <td class="py-4 px-6 text-slate-500 dark:text-slate-400 text-xs text-center font-medium">{{ loan.loan_date }}</td>
                                        <td class="py-4 px-6 text-slate-500 dark:text-slate-400 text-xs text-center font-medium">{{ loan.due_date }}</td>
                                        <!-- Status Badge -->
                                        <td class="py-4 px-6 text-right">
                                            <Badge
                                                variant="secondary"
                                                class="px-2.5 py-0.7 text-[9.5px] font-extrabold rounded-full border tracking-wide uppercase inline-block text-center"
                                                :class="{
                                                    'bg-blue-50/70 dark:bg-blue-950/20 text-blue-600 dark:text-blue-400 border-blue-100 dark:border-blue-900/30': loan.status === 'borrowed',
                                                    'bg-green-50/70 dark:bg-green-950/20 text-green-600 dark:text-green-400 border-green-100 dark:border-green-900/30': loan.status === 'returned',
                                                    'bg-red-50/70 dark:bg-red-950/20 text-red-600 dark:text-red-400 border-red-100 dark:border-red-900/30': loan.status === 'overdue',
                                                }">
                                                {{ loan.status === 'borrowed' ? 'Dipinjam' : loan.status === 'returned' ? 'Kembali' : 'Terlambat' }}
                                            </Badge>
                                        </td>
                                    </tr>
                                    <tr v-if="!summary?.recent_loans?.length">
                                        <td colspan="5" class="py-10 text-center text-slate-450 dark:text-slate-500 font-medium">Tidak ada transaksi terbaru.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </CardContent>
                </Card>

                <!-- Right: Quick Actions & Fines Quick Card (col-span-1) -->
                <div class="space-y-6">
                    <!-- Fines Balance Card (Premium Glass/Gradient Card) -->
                    <Card class="relative bg-linear-to-br from-blue-600 via-indigo-600 to-violet-750 border-0 p-6 rounded-2xl text-white flex flex-col justify-between shadow-lg shadow-blue-500/10 overflow-hidden h-[180px] group transition-all duration-300 hover:scale-[1.01] hover:shadow-xl hover:shadow-blue-500/20">
                        <div class="absolute right-[-10px] bottom-[-10px] translate-x-2 translate-y-2 opacity-15 group-hover:scale-110 transition-transform duration-500">
                            <Receipt class="w-40 h-40" />
                        </div>
                        <div class="space-y-2 z-10">
                            <p class="text-[10px] font-bold text-blue-100 uppercase tracking-widest">Total Denda Belum Bayar</p>
                            <h3 class="text-3.5xl font-black text-white tracking-tight drop-shadow-xs">
                                {{ formatRupiah(summary?.total_fines_unpaid ?? 0) }}
                            </h3>
                        </div>
                        <div class="z-10 mt-auto">
                            <Button @click="router.push('/fines')" variant="secondary" class="h-9 rounded-xl text-xs font-bold bg-white/15 hover:bg-white/25 text-white border-0 transition-all w-full shadow-xs">
                                Kelola Pembayaran Denda
                            </Button>
                        </div>
                    </Card>

                    <!-- Quick Actions Card -->
                    <Card class="bg-white dark:bg-slate-900 border border-slate-200/65 dark:border-slate-800/80 p-6 rounded-2xl shadow-sm space-y-4">
                        <CardTitle class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-widest">Aksi Cepat</CardTitle>
                        <div class="grid grid-cols-1 gap-3.5">
                            <button @click="router.push('/loans?action=borrow')" class="w-full flex items-center gap-4 p-3.5 border border-slate-100 dark:border-slate-850 hover:border-blue-500/50 dark:hover:border-blue-500/50 rounded-xl hover:bg-blue-500/[0.04] dark:hover:bg-blue-500/[0.04] transition-all duration-300 group bg-slate-50/20 dark:bg-slate-950/10 text-left cursor-pointer">
                                <div class="p-2.5 bg-blue-50 dark:bg-blue-950/60 rounded-xl text-blue-600 dark:text-blue-400 group-hover:scale-105 transition-transform">
                                    <PlusCircle class="w-5.5 h-5.5" />
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm font-bold text-slate-800 dark:text-slate-200">Peminjaman Baru</p>
                                    <p class="text-[10.5px] text-slate-400 dark:text-slate-500 font-medium">Proses transaksi peminjaman baru</p>
                                </div>
                            </button>
                            
                            <button @click="router.push('/loans')" class="w-full flex items-center gap-4 p-3.5 border border-slate-100 dark:border-slate-850 hover:border-green-500/50 dark:hover:border-green-500/50 rounded-xl hover:bg-green-500/[0.04] dark:hover:bg-green-500/[0.04] transition-all duration-300 group bg-slate-50/20 dark:bg-slate-950/10 text-left cursor-pointer">
                                <div class="p-2.5 bg-green-50 dark:bg-green-950/60 rounded-xl text-green-600 dark:text-green-400 group-hover:scale-105 transition-transform">
                                    <ArrowDownLeft class="w-5.5 h-5.5" />
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm font-bold text-slate-800 dark:text-slate-200">Pengembalian Item</p>
                                    <p class="text-[10.5px] text-slate-400 dark:text-slate-500 font-medium">Proses pengembalian koleksi item</p>
                                </div>
                            </button>

                            <button @click="router.push('/members?action=create')" class="w-full flex items-center gap-4 p-3.5 border border-slate-100 dark:border-slate-850 hover:border-purple-500/50 dark:hover:border-purple-500/50 rounded-xl hover:bg-purple-500/[0.04] dark:hover:bg-purple-500/[0.04] transition-all duration-300 group bg-slate-50/20 dark:bg-slate-950/10 text-left cursor-pointer">
                                <div class="p-2.5 bg-purple-50 dark:bg-purple-950/60 rounded-xl text-purple-600 dark:text-purple-400 group-hover:scale-105 transition-transform">
                                    <UserPlus class="w-5.5 h-5.5" />
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm font-bold text-slate-800 dark:text-slate-200">Daftar Anggota</p>
                                    <p class="text-[10.5px] text-slate-400 dark:text-slate-500 font-medium">Registrasi anggota baru perpustakaan</p>
                                </div>
                            </button>
                        </div>
                    </Card>
                </div>
            </div>
        </div>
    </div>
</template>
