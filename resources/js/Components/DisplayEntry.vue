<script setup>
defineProps({ node: Object });
</script>

<template>
    <div>
        <div class="mb-1 text-xs font-medium uppercase tracking-wide text-ink-3">{{ node.label }}</div>
        <span v-if="node.display === null || node.display === ''" class="text-ink-3">&mdash;</span>
        <svg
            v-else-if="node.type === 'boolean' && node.display"
            role="img" aria-label="Yes" viewBox="0 0 24 24"
            class="h-4 w-4 text-accent" fill="none" stroke="currentColor" stroke-width="2.4"
        ><path d="m20 6-11 11-5-5" /></svg>
        <span v-else-if="node.type === 'boolean'" aria-label="No" class="text-ink-3">&mdash;</span>
        <a v-else-if="node.type === 'file'" :href="node.display" target="_blank" rel="noopener" class="text-accent underline">View file</a>
        <div v-else-if="node.type === 'markdown'" class="whitespace-pre-wrap text-ink">{{ node.display }}</div>
        <span v-else class="text-ink">{{ node.display }}</span>
    </div>
</template>
