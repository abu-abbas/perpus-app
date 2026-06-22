import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import api from '@/lib/api';
import axios from 'axios';

/** Tipe data user. */
interface User {
    id: number;
    name: string;
    email: string;
    role: 'admin' | 'pustakawan';
}

/**
 * Store Pinia untuk state autentikasi.
 * Mengelola data user, login, logout, dan pengecekan sesi.
 */
export const useAuthStore = defineStore('auth', () => {
    const user = ref<User | null>(null);
    const loaded = ref(false);

    const isAuthenticated = computed(() => !!user.value);
    const isAdmin = computed(() => user.value?.role === 'admin');

    /**
     * Ambil data user dari API /user.
     * Dipanggil saat navigasi pertama dan setelah login.
     */
    async function fetchUser(): Promise<void> {
        try {
            const response = await api.get('/user');
            user.value = response.data.data;
        } catch {
            user.value = null;
        } finally {
            loaded.value = true;
        }
    }

    /**
     * Login via Sanctum SPA — kirim CSRF cookie dulu, lalu login.
     */
    async function login(email: string, password: string): Promise<void> {
        // Ambil CSRF cookie dari Sanctum
        await axios.get('/sanctum/csrf-cookie', { withCredentials: true });

        const response = await api.post('/login', { email, password });
        user.value = response.data.data;
        loaded.value = true;
    }

    /**
     * Logout — hapus sesi di server.
     */
    async function logout(): Promise<void> {
        await api.post('/logout');
        user.value = null;
    }

    return {
        user,
        loaded,
        isAuthenticated,
        isAdmin,
        fetchUser,
        login,
        logout,
    };
});
