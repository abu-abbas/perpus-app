<script setup lang="ts">
import { Plus, Edit2, Trash2, Search } from '@lucide/vue';
import { toTypedSchema } from '@vee-validate/zod';
import { useForm, useField } from 'vee-validate';
import { ref, watch } from 'vue';
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
import { useMembers } from '@/composables/useMembers';
import { useAuthStore } from '@/stores/auth';
import type { Member } from '@/types';

const authStore = useAuthStore();
const filters = ref({
    search: '',
    status: '',
    page: 1,
    per_page: 10,
});

const {
    membersResponse,
    members,
    isLoading,
    isError,
    createMember,
    isCreating,
    updateMember,
    isUpdating,
    deleteMember
} = useMembers(filters);

const isDialogOpen = ref(false);
const editingMember = ref<Member | null>(null);

// Skema Zod Validasi untuk Anggota
const memberSchema = z.object({
    full_name: z.string().min(1, 'Nama lengkap wajib diisi').max(255, 'Nama maksimal 255 karakter'),
    identity_number: z.string().min(5, 'Nomor identitas minimal 5 karakter').max(50, 'Nomor identitas maksimal 50 karakter'),
    phone: z.string().max(20, 'Nomor telepon maksimal 20 karakter').nullable().optional(),
    address: z.string().max(500, 'Alamat maksimal 500 karakter').nullable().optional(),
    status: z.enum(['active', 'inactive', 'suspended']).default('active'),
    join_date: z.string().optional(),
});

const { handleSubmit, errors, resetForm, setValues } = useForm({
    validationSchema: toTypedSchema(memberSchema),
    initialValues: {
        status: 'active',
        join_date: new Date().toISOString().split('T')[0],
    }
});

const { value: full_name } = useField<string>('full_name');
const { value: identity_number } = useField<string>('identity_number');
const { value: phone } = useField<string | undefined>('phone');
const { value: address } = useField<string | undefined>('address');
const { value: status } = useField<string>('status');
const { value: join_date } = useField<string>('join_date');

function openAddDialog() {
    editingMember.value = null;
    resetForm();
    full_name.value = '';
    identity_number.value = '';
    phone.value = '';
    address.value = '';
    status.value = 'active';
    join_date.value = new Date().toISOString().split('T')[0];
    isDialogOpen.value = true;
}

function openEditDialog(member: Member) {
    editingMember.value = member;
    resetForm();
    setValues({
        full_name: member.full_name,
        identity_number: member.identity_number,
        phone: member.phone ?? undefined,
        address: member.address ?? undefined,
        status: member.status,
        join_date: member.join_date,
    });
    isDialogOpen.value = true;
}

const onSubmit = handleSubmit(async (values: any) => {
    try {
        if (editingMember.value) {
            await updateMember({
                id: editingMember.value.id,
                data: {
                    full_name: values.full_name,
                    identity_number: values.identity_number,
                    phone: values.phone ?? null,
                    address: values.address ?? null,
                    status: values.status,
                },
            });
        } else {
            await createMember({
                full_name: values.full_name,
                identity_number: values.identity_number,
                phone: values.phone ?? null,
                address: values.address ?? null,
                join_date: values.join_date,
                status: values.status,
            });
        }

        isDialogOpen.value = false;
    } catch (e) {
        console.error(e);
    }
});

async function handleDelete(id: number) {
    if (confirm('Apakah Anda yakin ingin menghapus anggota ini? Riwayat peminjaman mungkin akan tetap ada tetapi status anggota terhapus.')) {
        try {
            await deleteMember(id);
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
                <h1 class="text-2xl font-bold tracking-tight">Anggota</h1>
                <p class="text-sm text-slate-500 dark:text-slate-400">
                    Kelola data keanggotaan perpustakaan.
                </p>
            </div>
            <Button
                @click="openAddDialog"
                class="flex items-center gap-2 px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-lg shadow-sm transition-all"
            >
                <Plus class="w-4 h-4" />
                Daftar Anggota Baru
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
                    placeholder="Cari nama, nomor anggota, atau identitas (KTP)..."
                    class="block w-full pl-10 pr-3 h-10 text-sm border border-slate-200 dark:border-slate-800 rounded-lg focus-visible:ring-blue-500 bg-transparent"
                />
            </div>

            <!-- Filter Status -->
            <select
                v-model="filters.status"
                class="block w-full h-10 px-3 py-2 text-sm border border-slate-200 dark:border-slate-800 rounded-lg focus:outline-none focus:ring-1 focus:ring-blue-500 bg-white dark:bg-slate-900 text-slate-700 dark:text-slate-200"
            >
                <option value="">Semua Status</option>
                <option value="active">Aktif</option>
                <option value="inactive">Nonaktif</option>
                <option value="suspended">Ditangguhkan</option>
            </select>
        </div>

        <!-- Loader / Error States -->
        <div v-if="isLoading" class="p-8 text-center text-slate-500">
            Memuat data anggota...
        </div>
        <div v-else-if="isError" class="p-4 rounded-xl bg-red-50 dark:bg-red-950/20 text-red-600 dark:text-red-400 text-sm border border-red-200 dark:border-red-900/50">
            Gagal mengambil data anggota.
        </div>

        <!-- Members Table -->
        <div v-else class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm overflow-hidden transition-colors">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-sm">
                    <thead>
                        <tr class="border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/20 text-slate-500 font-semibold">
                            <th class="py-3.5 px-6">No. Anggota</th>
                            <th class="py-3.5 px-6">Nama Lengkap</th>
                            <th class="py-3.5 px-6">Identitas / KTP</th>
                            <th class="py-3.5 px-6">Kontak / Alamat</th>
                            <th class="py-3.5 px-6">Tgl Gabung</th>
                            <th class="py-3.5 px-6">Status</th>
                            <th class="py-3.5 px-6 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800/80">
                        <tr v-for="member in members" :key="member.id" class="hover:bg-slate-50/40 dark:hover:bg-slate-800/10">
                            <!-- Member Number -->
                            <td class="py-4 px-6 font-mono text-xs font-semibold text-slate-900 dark:text-white">
                                {{ member.member_number }}
                            </td>
                            <!-- Full Name -->
                            <td class="py-4 px-6 font-semibold">{{ member.full_name }}</td>
                            <!-- Identity Number -->
                            <td class="py-4 px-6 text-slate-500 dark:text-slate-400">{{ member.identity_number }}</td>
                            <!-- Contact Details -->
                            <td class="py-4 px-6">
                                <div class="flex flex-col gap-0.5 max-w-50">
                                    <span class="text-xs text-slate-700 dark:text-slate-350 truncate">{{ member.phone || '-' }}</span>
                                    <span class="text-xs text-slate-400 truncate">{{ member.address || '-' }}</span>
                                </div>
                            </td>
                            <!-- Join Date -->
                            <td class="py-4 px-6 text-slate-500 dark:text-slate-400">{{ member.join_date }}</td>
                            <!-- Status -->
                            <td class="py-4 px-6">
                                <Badge
                                    variant="secondary"
                                    class="px-2.5 py-0.5 text-xs font-semibold rounded-full"
                                    :class="{
                                        'bg-green-50 dark:bg-green-950/40 text-green-600 dark:text-green-400 border border-green-100 dark:border-green-900/30': member.status === 'active',
                                        'bg-slate-100 dark:bg-slate-800 text-slate-650 dark:text-slate-400': member.status === 'inactive',
                                        'bg-red-50 dark:bg-red-950/40 text-red-600 dark:text-red-400 border border-red-100 dark:border-red-900/30': member.status === 'suspended',
                                    }">
                                    {{ member.status === 'active' ? 'Aktif' : member.status === 'inactive' ? 'Nonaktif' : 'Ditangguhkan' }}
                                </Badge>
                            </td>
                            <!-- Actions -->
                            <td class="py-4 px-6 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        @click="openEditDialog(member)"
                                        class="p-1.5 h-8 w-8 text-slate-500 hover:text-blue-600 dark:hover:text-blue-400 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-md transition-colors"
                                    >
                                        <Edit2 class="w-4 h-4" />
                                    </Button>
                                    <Button
                                        v-if="authStore.isAdmin"
                                        variant="ghost"
                                        size="icon"
                                        @click="handleDelete(member.id)"
                                        class="p-1.5 h-8 w-8 text-slate-500 hover:text-red-600 dark:hover:text-red-400 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-md transition-colors"
                                    >
                                        <Trash2 class="w-4 h-4" />
                                    </Button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="!members?.length">
                            <td colspan="7" class="py-8 text-center text-slate-400">Tidak ada anggota yang ditemukan.</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination footer -->
            <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-800 flex items-center justify-between">
                <span class="text-xs text-slate-500">
                    Menampilkan {{ membersResponse?.meta?.from || 0 }} - {{ membersResponse?.meta?.to || 0 }} dari {{ membersResponse?.meta?.total || 0 }} anggota
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
                        :disabled="!membersResponse?.links?.next"
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
            <DialogContent class="sm:max-w-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl p-6 transition-colors duration-300 max-h-[90vh] overflow-y-auto">
                <DialogHeader class="space-y-1">
                    <DialogTitle class="text-lg font-bold">
                        {{ editingMember ? 'Perbarui Data Anggota' : 'Daftarkan Anggota Baru' }}
                    </DialogTitle>
                </DialogHeader>
                <form @submit.prevent="onSubmit" class="space-y-4">
                    <!-- Nama Lengkap -->
                    <div class="space-y-1.5">
                        <Label class="text-xs font-semibold text-slate-550 dark:text-slate-400 uppercase tracking-wider">Nama Lengkap</Label>
                        <Input
                            v-model="full_name"
                            type="text"
                            placeholder="Masukkan nama lengkap..."
                            class="block w-full px-3 py-2 border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-850 rounded-lg text-sm focus-visible:ring-blue-500"
                            :class="[errors.full_name ? 'border-red-500 focus-visible:ring-red-500' : '']"
                        />
                        <p v-if="errors.full_name" class="text-xs text-red-500">{{ errors.full_name }}</p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- Nomor Identitas -->
                        <div class="space-y-1.5">
                            <Label class="text-xs font-semibold text-slate-550 dark:text-slate-400 uppercase tracking-wider">No. Identitas (KTP/SIM)</Label>
                            <Input
                                v-model="identity_number"
                                type="text"
                                placeholder="Masukkan nomor identitas..."
                                class="block w-full px-3 py-2 border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-850 rounded-lg text-sm focus-visible:ring-blue-500"
                                :class="[errors.identity_number ? 'border-red-500 focus-visible:ring-red-500' : '']"
                            />
                            <p v-if="errors.identity_number" class="text-xs text-red-500">{{ errors.identity_number }}</p>
                        </div>

                        <!-- Telepon -->
                        <div class="space-y-1.5">
                            <Label class="text-xs font-semibold text-slate-550 dark:text-slate-400 uppercase tracking-wider">Nomor Telepon</Label>
                            <Input
                                v-model="phone"
                                type="text"
                                placeholder="Nomor telepon (opsional)..."
                                class="block w-full px-3 py-2 border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-850 rounded-lg text-sm focus-visible:ring-blue-500"
                                :class="[errors.phone ? 'border-red-500 focus-visible:ring-red-500' : '']"
                            />
                            <p v-if="errors.phone" class="text-xs text-red-500">{{ errors.phone }}</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- Tanggal Gabung -->
                        <div class="space-y-1.5">
                            <Label class="text-xs font-semibold text-slate-550 dark:text-slate-400 uppercase tracking-wider">Tanggal Bergabung</Label>
                            <Input
                                v-model="join_date"
                                type="date"
                                :disabled="!!editingMember"
                                class="block w-full px-3 py-2 border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-850 rounded-lg text-sm focus-visible:ring-blue-500 disabled:opacity-50"
                            />
                        </div>

                        <!-- Status -->
                        <div class="space-y-1.5">
                            <Label class="text-xs font-semibold text-slate-550 dark:text-slate-400 uppercase tracking-wider">Status Keanggotaan</Label>
                            <select
                                v-model="status"
                                class="block w-full h-10 px-3 py-2 border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-850 rounded-lg text-sm text-slate-700 dark:text-slate-200 focus:outline-none focus:ring-1 focus:ring-blue-500"
                            >
                                <option value="active">Aktif</option>
                                <option value="inactive">Nonaktif</option>
                                <option value="suspended">Ditangguhkan</option>
                            </select>
                        </div>
                    </div>

                    <!-- Alamat -->
                    <div class="space-y-1.5">
                        <Label class="text-xs font-semibold text-slate-550 dark:text-slate-400 uppercase tracking-wider">Alamat Rumah</Label>
                        <textarea
                            v-model="address"
                            rows="3"
                            placeholder="Alamat lengkap..."
                            class="block w-full px-3 py-2 border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-850 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                            :class="[errors.address ? 'border-red-500' : '']"
                        ></textarea>
                        <p v-if="errors.address" class="text-xs text-red-500">{{ errors.address }}</p>
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
    </div>
</template>
