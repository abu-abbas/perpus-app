import axios from 'axios';
import { toast } from 'vue-sonner';
import router from '@/router';
import { useAuthStore } from '@/stores/auth';

/**
 * Instance axios global untuk API Sanctum SPA.
 */
const axiosInstance = axios.create({
    baseURL: '/api',
    headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
    },
    withCredentials: true,
    withXSRFToken: true,
});

// Interceptor untuk menangani error secara global
axiosInstance.interceptors.response.use(
    (response) => response,
    async (error) => {
        const status = error.response?.status;
        const data = error.response?.data;

        if (status === 401) {
            // Sesi kedaluwarsa atau tidak terautentikasi
            const authStore = useAuthStore();
            authStore.user = null;
            authStore.loaded = true;

            if (router.currentRoute.value.name !== 'login') {
                toast.error('Sesi Anda telah berakhir. Silakan login kembali.');
                router.push({ name: 'login' });
            }
        } else if (status === 403) {
            toast.error(data?.message || 'Anda tidak memiliki akses untuk melakukan aksi ini.');
        } else if (status === 422) {
            // Validasi form atau BusinessException dari Laravel
            const message = data?.message || 'Data yang Anda masukkan tidak valid.';
            toast.warning(message);
        } else if (status === 500) {
            toast.error('Terjadi kesalahan sistem. Silakan coba beberapa saat lagi.');
        } else if (error.message === 'Network Error') {
            toast.error('Koneksi internet terputus atau server tidak dapat dijangkau.');
        }

        return Promise.reject(error);
    }
);

export default axiosInstance;
