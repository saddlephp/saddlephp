<script setup>
import { computed } from 'vue';

const props = defineProps({
    values: { type: Array, default: () => [] },
    labels: { type: Array, default: () => [] },
    height: { type: Number, default: 48 },
    showLabels: { type: Boolean, default: false },
});

const max = computed(() => Math.max(1, ...props.values));
const barW = computed(() => 100 / Math.max(1, props.values.length));
</script>

<template>
    <div>
        <svg :viewBox="`0 0 100 ${height}`" preserveAspectRatio="none" class="w-full" :style="{ height: `${height}px` }">
            <rect
                v-for="(v, i) in values"
                :key="i"
                :x="i * barW + barW * 0.15"
                :width="barW * 0.7"
                :y="height - (v / max) * height"
                :height="(v / max) * height"
                rx="1"
                class="fill-accent"
            />
        </svg>
        <div v-if="showLabels" class="mt-1 flex text-[0.65rem] text-ink-3">
            <span v-for="(l, i) in labels" :key="i" class="truncate text-center" :style="{ width: `${barW}%` }">{{ l }}</span>
        </div>
    </div>
</template>
