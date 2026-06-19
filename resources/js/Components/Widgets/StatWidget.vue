<script setup>
import BarChart from '../BarChart.vue';

defineProps({ widget: Object });
</script>

<template>
    <div class="rounded-xl border border-line bg-bg p-5">
        <p class="text-xs font-medium uppercase tracking-wide text-ink-3">{{ widget.label }}</p>
        <p class="mt-1 text-3xl font-semibold tracking-tight">{{ widget.value }}</p>
        <p v-if="widget.description" class="mt-0.5 text-xs text-ink-2">{{ widget.description }}</p>
        <BarChart v-if="widget.chart && widget.chart.length" :values="widget.chart" :height="32" class="mt-3" />
    </div>
</template>
