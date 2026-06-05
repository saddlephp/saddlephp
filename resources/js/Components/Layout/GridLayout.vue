<script setup>
import { computed } from 'vue';
import { flattenFields } from '../../support/flattenFields';
import FormRenderer from '../FormRenderer.vue';

const props = defineProps({ node: Object, form: Object });

const columns = computed(() => Math.max(1, props.node.columns ?? 1));

const hasFields = computed(() => flattenFields(props.node.schema).length > 0);

// A child's columnSpan (serialized as `span` on leaves) is clamped to the grid
// width. Only leaves carry a span; nested containers default to 1.
function spanStyle(child) {
    const span = Math.min(columns.value, Math.max(1, child.span ?? 1));
    return { gridColumn: `span ${span} / span ${span}` };
}
</script>

<template>
    <div
        v-if="hasFields"
        class="grid grid-cols-1 gap-5 sm:[grid-template-columns:repeat(var(--saddle-grid-cols),minmax(0,1fr))]"
        :style="{ '--saddle-grid-cols': columns }"
    >
        <div v-for="(child, index) in node.schema" :key="child.name ?? `grid-${index}`" :style="spanStyle(child)">
            <FormRenderer :fields="[child]" :form="form" />
        </div>
    </div>
</template>
