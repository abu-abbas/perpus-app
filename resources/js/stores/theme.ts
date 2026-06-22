import { defineStore } from 'pinia';
import { ref, watch } from 'vue';

export type Theme = 'light' | 'dark' | 'system';

/**
 * Store Pinia untuk mengelola tema (light, dark, atau system).
 * Menyimpan preferensi di localStorage dan mensinkronisasikan class 'dark' pada element HTML.
 */
export const useThemeStore = defineStore('theme', () => {
    const theme = ref<Theme>((localStorage.getItem('theme') as Theme) || 'system');

    function applyTheme(newTheme: Theme) {
        const root = window.document.documentElement;
        root.classList.remove('light', 'dark');

        if (newTheme === 'system') {
            const systemTheme = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
            root.classList.add(systemTheme);
        } else {
            root.classList.add(newTheme);
        }
    }

    // Set tema awal
    applyTheme(theme.value);

    // Watch perubahan tema dan simpan ke localStorage
    watch(theme, (newTheme) => {
        localStorage.setItem('theme', newTheme);
        applyTheme(newTheme);
    });

    // Dengarkan perubahan system preference jika diset ke 'system'
    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', () => {
        if (theme.value === 'system') {
            applyTheme('system');
        }
    });

    function setTheme(newTheme: Theme) {
        theme.value = newTheme;
    }

    return {
        theme,
        setTheme,
    };
});
