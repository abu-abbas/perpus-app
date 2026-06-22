<script setup lang="ts">
import { Lock, Mail, Eye, EyeOff } from '@lucide/vue';
import { toTypedSchema } from '@vee-validate/zod';
import { useForm, useField } from 'vee-validate';
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { toast } from 'vue-sonner';
import { z } from 'zod';

import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { useAuthStore } from '@/stores/auth';

const authStore = useAuthStore();
const router = useRouter();
const showPassword = ref(false);
const isSubmitting = ref(false);

// Skema Validasi Zod
const loginSchema = z.object({
    email: z.string().min(1, 'Email wajib diisi').email('Format email tidak valid'),
    password: z.string().min(6, 'Password minimal 6 karakter'),
});

const { handleSubmit, errors } = useForm({
    validationSchema: toTypedSchema(loginSchema),
});

const { value: email } = useField<string>('email');
const { value: password } = useField<string>('password');

// Default credentials for ease of test/demo
email.value = 'admin@perpus.test';
password.value = 'password';

const onSubmit = handleSubmit(async (values: any) => {
    isSubmitting.value = true;

    try {
        await authStore.login(values.email, values.password);
        toast.success('Login berhasil! Selamat datang kembali.');
        router.push({ name: 'dashboard' });
    } catch (error: any) {
        console.error('Gagal login:', error);
    } finally {
        isSubmitting.value = ref(false).value;
    }
});
</script>

<template>
    <div class="min-h-screen bg-slate-50 dark:bg-slate-950 flex flex-col justify-center py-12 px-4 sm:px-6 lg:px-8 transition-colors duration-300">
        <div class="sm:mx-auto sm:w-full sm:max-w-md">
            <Card class="border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 shadow-xl rounded-2xl transition-colors duration-300">
                <CardHeader class="space-y-1 text-center pb-6">
                    <CardTitle class="text-3xl font-extrabold bg-linear-to-r from-blue-600 to-indigo-600 dark:from-blue-400 dark:to-indigo-400 bg-clip-text text-transparent">
                        Sistem Perpustakaan
                    </CardTitle>
                    <CardDescription class="text-sm text-slate-500 dark:text-slate-400">
                        Silakan masuk untuk mengelola perpustakaan
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="onSubmit" class="space-y-5">
                        <!-- Email field -->
                        <div class="space-y-1.5">
                            <Label for="email" class="text-sm font-medium text-slate-700 dark:text-slate-300">
                                Alamat Email
                            </Label>
                            <div class="relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400 dark:text-slate-500">
                                    <Mail class="h-5 w-5" />
                                </div>
                                <Input
                                    id="email"
                                    v-model="email"
                                    type="email"
                                    autocomplete="email"
                                    class="pl-10 pr-3 h-10 block w-full rounded-lg focus-visible:ring-blue-500 border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100"
                                    :class="[errors.email ? 'border-red-500 focus-visible:ring-red-500' : '']"
                                    placeholder="nama@email.com"
                                />
                            </div>
                            <p v-if="errors.email" class="text-xs text-red-650 dark:text-red-400">{{ errors.email }}</p>
                        </div>

                        <!-- Password field -->
                        <div class="space-y-1.5">
                            <Label for="password" class="text-sm font-medium text-slate-700 dark:text-slate-300">
                                Kata Sandi
                            </Label>
                            <div class="relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400 dark:text-slate-500">
                                    <Lock class="h-5 w-5" />
                                </div>
                                <Input
                                    id="password"
                                    v-model="password"
                                    :type="showPassword ? 'text' : 'password'"
                                    autocomplete="current-password"
                                    class="pl-10 pr-10 h-10 block w-full rounded-lg focus-visible:ring-blue-500 border border-slate-300 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100"
                                    :class="[errors.password ? 'border-red-500 focus-visible:ring-red-500' : '']"
                                    placeholder="••••••••"
                                />
                                <button
                                    type="button"
                                    @click="showPassword = !showPassword"
                                    class="absolute inset-y-0 right-0 pr-3 flex items-center text-slate-400 dark:text-slate-500 hover:text-slate-600 dark:hover:text-slate-300"
                                >
                                    <EyeOff v-if="showPassword" class="h-5 w-5" />
                                    <Eye v-else class="h-5 w-5" />
                                </button>
                            </div>
                            <p v-if="errors.password" class="text-xs text-red-650 dark:text-red-400">{{ errors.password }}</p>
                        </div>

                        <!-- Submit Button -->
                        <div class="pt-2">
                            <Button
                                type="submit"
                                :disabled="isSubmitting"
                                class="w-full flex justify-center py-2.5 rounded-lg shadow-sm text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 transition-all duration-200"
                            >
                                <span v-if="isSubmitting" class="flex items-center gap-2">
                                    <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    Memproses...
                                </span>
                                <span v-else>Masuk</span>
                            </Button>
                        </div>
                    </form>

                    <!-- Demo Account Box -->
                    <div class="mt-6 p-4 bg-slate-50 dark:bg-slate-800/50 rounded-lg border border-slate-100 dark:border-slate-800/80 text-xs text-slate-500 dark:text-slate-400 space-y-1">
                        <p class="font-semibold text-slate-700 dark:text-slate-350">Akun Demo (Ujian):</p>
                        <p>• Admin: <code class="text-blue-600 dark:text-blue-400">admin@perpus.test</code> (password: <code class="text-blue-600 dark:text-blue-400">password</code>)</p>
                        <p>• Pustakawan: <code class="text-blue-600 dark:text-blue-400">pustakawan@perpus.test</code> (password: <code class="text-blue-600 dark:text-blue-400">password</code>)</p>
                    </div>
                </CardContent>
            </Card>
        </div>
    </div>
</template>
