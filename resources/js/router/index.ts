import { createRouter, createWebHistory, type RouteRecordRaw } from 'vue-router';
import { useAuthStore } from '@/stores/auth';

/**
 * Definisi route SPA perpustakaan.
 * Lazy-loading digunakan untuk semua halaman agar bundle lebih kecil.
 */
const routes: RouteRecordRaw[] = [
    {
        path: '/login',
        name: 'login',
        component: () => import('@/pages/LoginPage.vue'),
        meta: { guest: true },
    },
    {
        path: '/',
        component: () => import('@/layouts/DashboardLayout.vue'),
        meta: { requiresAuth: true },
        children: [
            {
                path: '',
                name: 'dashboard',
                component: () => import('@/pages/DashboardPage.vue'),
            },
            {
                path: 'categories',
                name: 'categories',
                component: () => import('@/pages/CategoriesPage.vue'),
            },
            {
                path: 'items',
                name: 'items',
                component: () => import('@/pages/ItemsPage.vue'),
            },
            {
                path: 'members',
                name: 'members',
                component: () => import('@/pages/MembersPage.vue'),
            },
            {
                path: 'loans',
                name: 'loans',
                component: () => import('@/pages/LoansPage.vue'),
            },
            {
                path: 'fines',
                name: 'fines',
                component: () => import('@/pages/FinesPage.vue'),
            },
            {
                path: 'reports',
                name: 'laporan',
                component: () => import('@/pages/ReportsPage.vue'),
            },
        ],
    },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

/**
 * Navigation guard: cek autentikasi sebelum navigasi.
 */
router.beforeEach(async (to, _from, next) => {
    const authStore = useAuthStore();

    // Muat data user jika belum dimuat
    if (!authStore.loaded) {
        await authStore.fetchUser();
    }

    // Redirect ke login jika butuh auth tapi belum login
    if (to.meta.requiresAuth && !authStore.isAuthenticated) {
        return next({ name: 'login' });
    }

    // Redirect ke dashboard jika sudah login tapi akses halaman guest
    if (to.meta.guest && authStore.isAuthenticated) {
        return next({ name: 'dashboard' });
    }

    next();
});

export default router;
