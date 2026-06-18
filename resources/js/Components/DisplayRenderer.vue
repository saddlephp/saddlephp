<script setup>
import DisplayEntry from './DisplayEntry.vue';

// Recursive: walks the same layout tree the form serializer emits, but renders
// each leaf read-only. The form-layout components are coupled to FormRenderer
// and the live form object, so the view page gets its own thin renderer.
defineOptions({ name: 'DisplayRenderer' });
defineProps({ nodes: Array });

function span(node, child) {
    const width = Math.max(1, node.columns ?? 1);
    const s = Math.min(width, Math.max(1, child.span ?? 1));
    return { gridColumn: `span ${s} / span ${s}` };
}
</script>

<template>
    <div class="space-y-6">
        <template v-for="(node, index) in nodes" :key="node.name ?? `node-${index}`">
            <!-- Section -->
            <section v-if="node.layout === 'section'">
                <h2 class="text-sm font-semibold">{{ node.label }}</h2>
                <p v-if="node.description" class="mb-3 text-xs text-ink-3">{{ node.description }}</p>
                <DisplayRenderer :nodes="node.schema" />
            </section>
            <!-- Grid -->
            <div
                v-else-if="node.layout === 'grid'"
                class="grid grid-cols-1 gap-5 sm:[grid-template-columns:repeat(var(--c),minmax(0,1fr))]"
                :style="{ '--c': Math.max(1, node.columns ?? 1) }"
            >
                <div v-for="(child, i) in node.schema" :key="child.name ?? `g-${i}`" :style="span(node, child)">
                    <DisplayRenderer :nodes="[child]" />
                </div>
            </div>
            <!-- Tabs (stacked sections on the read-only page) -->
            <div v-else-if="node.layout === 'tabs'" class="space-y-6">
                <section v-for="(tab, t) in node.tabs" :key="`tab-${t}`">
                    <h3 class="mb-2 text-sm font-semibold">{{ tab.label }}</h3>
                    <DisplayRenderer :nodes="tab.schema" />
                </section>
            </div>
            <!-- Leaf -->
            <DisplayEntry v-else :node="node" />
        </template>
    </div>
</template>
