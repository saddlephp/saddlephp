<script setup>
import { useTheme } from '../support/useTheme';

const { mode, setMode } = useTheme();
const options = [
    { value: 'light', label: 'Light' },
    { value: 'dark', label: 'Dark' },
    { value: 'system', label: 'System' },
];
</script>

<template>
    <div class="flex gap-1 rounded-lg border border-line-2 p-0.5 text-xs">
        <button
            v-for="o in options"
            :key="o.value"
            type="button"
            class="flex-1 rounded-md px-2 py-1 transition"
            :class="mode === o.value ? 'bg-ink text-white' : 'text-ink-2 hover:bg-surface-2'"
            @click="setMode(o.value)"
        >{{ o.label }}</button>
    </div>
</template>
