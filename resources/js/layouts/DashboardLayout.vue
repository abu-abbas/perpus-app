<script setup lang="ts">
import {
    LayoutDashboard,
    Tags,
    BookOpen,
    Users,
    ClipboardList,
    Receipt,
    FileText,
    LogOut,
    Sun,
    Moon,
    Menu
} from '@lucide/vue';
import { ref } from 'vue';
import { useRouter, useRoute } from 'vue-router';

import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger
} from '@/components/ui/dropdown-menu';
import {
    Sheet,
    SheetContent,
    SheetHeader,
    SheetTitle,
    SheetTrigger
} from '@/components/ui/sheet';
import { useAuthStore } from '@/stores/auth';
import { useThemeStore } from '@/stores/theme';


const authStore = useAuthStore();
const themeStore = useThemeStore();
const router = useRouter();
const route = useRoute();

const isMobileMenuOpen = ref(false);

const navigationItems = [
    { name: 'Dashboard', path: '/', icon: LayoutDashboard },
    { name: 'Kategori', path: '/categories', icon: Tags },
    { name: 'Item', path: '/items', icon: BookOpen },
    { name: 'Anggota', path: '/members', icon: Users },
    { name: 'Peminjaman', path: '/loans', icon: ClipboardList },
    { name: 'Denda', path: '/fines', icon: Receipt },
    { name: 'Laporan', path: '/reports', icon: FileText },
];

async function handleLogout() {
    try {
        await authStore.logout();
        router.push({ name: 'login' });
    } catch (error) {
        console.error('Gagal logout:', error);
    }
}

function toggleTheme() {
    themeStore.setTheme(themeStore.theme === 'dark' ? 'light' : 'dark');
}
</script>

<template>
    <div class="min-h-screen bg-slate-50 dark:bg-slate-950 text-slate-900 dark:text-slate-100 flex flex-col transition-colors duration-300">
        <!-- Top Navigation Header (dashboard-01 style) -->
        <header class="sticky top-0 z-50 h-16 w-full flex items-center justify-between px-4 md:px-8 bg-white/80 dark:bg-slate-900/80 backdrop-blur-md border-b border-slate-200/60 dark:border-slate-800/80 shadow-xs transition-colors duration-300">
            <div class="flex items-center gap-6 lg:gap-8">
                <!-- App Logo -->
                <router-link to="/" class="flex items-center gap-2">
                    <span class="text-xl font-black tracking-tight bg-linear-to-r from-blue-600 to-indigo-600 dark:from-blue-400 dark:to-indigo-400 bg-clip-text text-transparent transition-transform hover:scale-102">
                        Perpus App
                    </span>
                </router-link>

                <!-- Desktop Navigation Links -->
                <nav class="hidden md:flex items-center gap-1.5">
                    <router-link
                        v-for="item in navigationItems"
                        :key="item.name"
                        :to="item.path"
                        class="flex items-center gap-2 px-3 py-2 rounded-lg text-sm font-semibold transition-all duration-200"
                        :class="[
                            route.path === item.path
                                ? 'bg-blue-50 dark:bg-blue-950/50 text-blue-600 dark:text-blue-400'
                                : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800/50 hover:text-slate-900 dark:hover:text-slate-100'
                        ]"
                    >
                        <component :is="item.icon" class="w-4 h-4" />
                        {{ item.name }}
                    </router-link>
                </nav>
            </div>

            <!-- Header Right Menu -->
            <div class="flex items-center gap-3">
                <!-- Theme Toggle -->
                <Button
                    variant="ghost"
                    size="icon"
                    @click="toggleTheme"
                    class="h-9 w-9 text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800/60 transition-colors rounded-lg"
                    aria-label="Toggle Theme"
                >
                    <Sun v-if="themeStore.theme === 'dark'" class="w-4.5 h-4.5 text-amber-500" />
                    <Moon v-else class="w-4.5 h-4.5 text-indigo-600 dark:text-indigo-400" />
                </Button>

                <!-- User Profile Dropdown -->
                <DropdownMenu>
                    <DropdownMenuTrigger as-child>
                        <Button variant="ghost" class="relative h-9 w-9 rounded-full flex items-center justify-center bg-slate-100 dark:bg-slate-800 focus-visible:ring-0">
                            <div class="w-7 h-7 rounded-full bg-blue-100 dark:bg-blue-900/60 flex items-center justify-center text-blue-600 dark:text-blue-300 font-bold text-xs uppercase">
                                {{ authStore.user?.name.charAt(0).toUpperCase() }}
                            </div>
                        </Button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent align="end" class="w-56 mt-2 rounded-xl border border-slate-200 dark:border-slate-850 bg-white dark:bg-slate-900 p-1 shadow-lg">
                        <DropdownMenuLabel class="px-2 py-1.5 text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                            Akun Saya
                        </DropdownMenuLabel>
                        <div class="px-2 py-1.5">
                            <p class="text-sm font-semibold truncate text-slate-900 dark:text-white">{{ authStore.user?.name }}</p>
                            <p class="text-xs text-slate-400 dark:text-slate-500 capitalize truncate">{{ authStore.user?.role }}</p>
                        </div>
                        <DropdownMenuSeparator class="h-px bg-slate-100 dark:bg-slate-800 my-1" />
                        <DropdownMenuItem @click="handleLogout" class="flex items-center gap-2 px-2 py-2 text-sm text-red-650 hover:bg-red-50 dark:hover:bg-red-950/20 rounded-md cursor-pointer">
                            <LogOut class="w-4 h-4" />
                            <span>Keluar Aplikasi</span>
                        </DropdownMenuItem>
                    </DropdownMenuContent>
                </DropdownMenu>

                <!-- Mobile Hamburger Menu Button -->
                <Sheet v-model:open="isMobileMenuOpen">
                    <SheetTrigger as-child>
                        <Button
                            variant="ghost"
                            size="icon"
                            class="md:hidden h-9 w-9 text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800/60 rounded-lg"
                        >
                            <Menu class="w-5 h-5" />
                        </Button>
                    </SheetTrigger>
                    <SheetContent side="left" class="w-72 bg-white dark:bg-slate-900 border-r border-slate-200 dark:border-slate-800 p-6 flex flex-col justify-between">
                        <div class="space-y-6">
                            <SheetHeader class="text-left">
                                <SheetTitle class="text-xl font-black bg-linear-to-r from-blue-600 to-indigo-600 dark:from-blue-400 dark:to-indigo-400 bg-clip-text text-transparent">
                                    Perpus App
                                </SheetTitle>
                            </SheetHeader>

                            <nav class="space-y-1">
                                <router-link
                                    v-for="item in navigationItems"
                                    :key="item.name"
                                    :to="item.path"
                                    @click="isMobileMenuOpen = false"
                                    class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-semibold transition-all duration-200"
                                    :class="[
                                        route.path === item.path
                                            ? 'bg-blue-50 dark:bg-blue-950/50 text-blue-600 dark:text-blue-400'
                                            : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-slate-800/50'
                                    ]"
                                >
                                    <component :is="item.icon" class="w-4.5 h-4.5" />
                                    {{ item.name }}
                                </router-link>
                            </nav>
                        </div>

                        <!-- Mobile Menu Footer -->
                        <div class="space-y-4 pt-6 border-t border-slate-100 dark:border-slate-800/80">
                            <div class="flex items-center gap-3 p-2 rounded-xl bg-slate-50 dark:bg-slate-800/40">
                                <div class="w-10 h-10 rounded-full bg-blue-100 dark:bg-blue-900 flex items-center justify-center text-blue-600 dark:text-blue-300 font-bold uppercase text-sm">
                                    {{ authStore.user?.name.charAt(0).toUpperCase() }}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-semibold truncate">{{ authStore.user?.name }}</p>
                                    <p class="text-xs text-slate-500 dark:text-slate-400 capitalize truncate">{{ authStore.user?.role }}</p>
                                </div>
                            </div>
                            <Button
                                @click="handleLogout"
                                variant="outline"
                                class="w-full flex items-center justify-center gap-2 border-red-200 dark:border-red-900/30 text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-955/20 h-10 rounded-xl font-bold"
                            >
                                <LogOut class="w-4 h-4" />
                                Keluar
                            </Button>
                        </div>
                    </SheetContent>
                </Sheet>
            </div>
        </header>

        <!-- Route Pages Content -->
        <main class="flex-1 px-4 py-8 md:px-8 overflow-y-auto max-w-7xl mx-auto w-full">
            <router-view />
        </main>
    </div>
</template>
