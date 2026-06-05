<script setup>
import { computed } from 'vue';
import { flattenFields } from '../../support/flattenFields';
import FormRenderer from '../FormRenderer.vue';

const props = defineProps({ node: Object, form: Object });

// Hide a section whose (recursive) schema has no leaf fields to render.
const hasFields = computed(() => flattenFields(props.node.schema).length > 0);
</script>

<template>
    <section v-if="hasFields" class="rounded-xl border border-line bg-bg p-5">
        <h2 class="text-base font-semibold tracking-tight">{{ node.label }}</h2>
        <p v-if="node.description" class="mt-1 text-sm text-ink-3">{{ node.description }}</p>
        <div class="mt-4">
            <FormRenderer :fields="node.schema" :form="form" />
        </div>
    </section>
</template>
