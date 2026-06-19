<script setup>
import StatWidget from './StatWidget.vue';
import ChartWidget from './ChartWidget.vue';

defineProps({ widgets: Array });

const map = { 'stat-widget': StatWidget, 'chart-widget': ChartWidget };
</script>

<template>
    <div v-if="widgets.length" class="mb-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <component
            :is="map[w.component]"
            v-for="(w, i) in widgets"
            :key="i"
            :widget="w"
            :class="w.component === 'chart-widget' ? 'sm:col-span-2 lg:col-span-4' : ''"
        />
    </div>
</template>
