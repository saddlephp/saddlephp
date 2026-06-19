<script setup>
import BarChart from '../BarChart.vue';

defineProps({ widget: Object });
</script>

<template>
    <div class="rounded-xl border border-line bg-bg p-5">
        <p class="text-sm font-semibold">{{ widget.heading }}</p>
        <BarChart :values="widget.values" :labels="widget.labels" :height="160" :show-labels="true" class="mt-3" />
    </div>
</template>
