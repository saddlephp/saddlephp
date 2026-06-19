import { ref } from 'vue';

const mode = ref(localStorage.getItem('saddle-theme') || 'system');

function apply(value) {
    const dark = value === 'dark'
        || (value === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches);
    document.documentElement.classList.toggle('dark', dark);
}

export function useTheme() {
    function setMode(value) {
        mode.value = value;
        localStorage.setItem('saddle-theme', value);
        apply(value);
    }

    return { mode, setMode };
}
