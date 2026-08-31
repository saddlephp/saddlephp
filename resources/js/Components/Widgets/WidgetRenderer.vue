<script setup>
import StatWidget from './StatWidget.vue';
import ChartWidget from './ChartWidget.vue';
import { resolveWidget } from '../../registry';

defineProps({ widgets: Array });

const map = { 'stat-widget': StatWidget, 'chart-widget': ChartWidget };

// Consulted before the built-in map, so Widget::$component is no longer limited
// to the two shipped values and the abstract base is open to subclassing.
const widgetFor = (name) => resolveWidget(name, map);
</script>

<template>
    <div v-if="widgets.length" class="mb-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <component
            :is="widgetFor(w.component)"
            v-for="(w, i) in widgets"
            :key="i"
            :widget="w"
            :class="w.component === 'chart-widget' ? 'sm:col-span-2 lg:col-span-4' : ''"
        />
    </div>
</template>
