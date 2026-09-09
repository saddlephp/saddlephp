<script setup>
import { useI18n } from 'vue-i18n';

defineProps({ node: Object });

const { t } = useI18n();
</script>

<template>
    <div>
        <div class="mb-1 text-xs font-medium uppercase tracking-wide text-ink-3">{{ node.label }}</div>
        <span v-if="node.display === null || node.display === ''" :aria-label="t('booleans.unknown')" class="text-ink-3">&mdash;</span>
        <svg
            v-else-if="node.type === 'boolean' && node.display"
            role="img" :aria-label="t('booleans.yes')" viewBox="0 0 24 24"
            class="h-4 w-4 text-accent" fill="none" stroke="currentColor" stroke-width="2.4"
        ><path d="m20 6-11 11-5-5" /></svg>
        <svg
            v-else-if="node.type === 'boolean'"
            role="img" :aria-label="t('booleans.no')" viewBox="0 0 24 24"
            class="h-4 w-4 text-ink-3" fill="none" stroke="currentColor" stroke-width="2.4"
        ><path d="M18 6 6 18M6 6l12 12" /></svg>
        <a v-else-if="node.type === 'file'" :href="node.display" target="_blank" rel="noopener" class="text-accent underline">View file</a>
        <div v-else-if="node.type === 'markdown'" class="whitespace-pre-wrap text-ink">{{ node.display }}</div>
        <span v-else class="text-ink">{{ node.display }}</span>
    </div>
</template>
